<?php

namespace App\Services\Feeds;

use App\Contracts\Feeds\FeedParserInterface;
use App\Data\Feeds\ParsedFeedItem;
use App\Models\Feed;
use App\Models\FeedItem;
use App\Services\Feeds\Parsers\JsonFeedParser;
use App\Services\Feeds\Parsers\XmlFeedParser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FeedFetcher
{
    public function __construct(
        private readonly XmlFeedParser $xmlParser,
        private readonly JsonFeedParser $jsonParser,
    ) {
    }

    /**
     * Fetch and persist items for a given feed.
     */
    public function fetch(Feed $feed): int
    {
        $feed->update(['status' => 'syncing', 'error_message' => null]);

        try {
            $response = Http::timeout(20)->withOptions(['verify' => false])->get($feed->url);
            if (! $response->ok()) {
                throw new \RuntimeException('Failed to fetch feed: HTTP '.$response->status());
            }

            $body = (string) $response->body();
            $parser = $this->resolveParser($feed->type);
            $parsed = $parser->parse($body, $feed->url);

            // Update feed source name
            if (! empty($parsed->source)) {
                $feed->source = $parsed->source;
            }

            $now = now();
            $inserted = 0;

            foreach ($parsed->items as $item) {
                if (! $item instanceof ParsedFeedItem) {
                    continue;
                }

                $guid = $item->guid ?: ($item->url ?? null);
                if ($guid === null) {
                    // Skip items that can't be deduped
                    continue;
                }

                $model = FeedItem::firstOrNew([
                    'feed_id' => $feed->id,
                    'guid' => (string) $guid,
                ]);

                $model->fill([
                    'user_id' => $feed->user_id,
                    'title' => $item->title,
                    'url' => $item->url,
                    'content' => $item->content,
                    'summary' => $item->summary,
                    'author' => $item->author,
                    'tags' => $item->tags,
                    'published_at' => $item->publishedAt ? \Carbon\CarbonImmutable::instance($item->publishedAt) : null,
                    'fetched_at' => $now,
                ]);

                $wasRecentlyCreated = ! $model->exists;
                $model->save();

                if ($wasRecentlyCreated) {
                    $inserted++;
                    // Index new items for search
                    $model->searchable();
                }
            }

            $feed->last_fetched_at = $now;
            $latest = $feed->items()->max('published_at');
            if ($latest) {
                $feed->last_item_published_at = $latest;
            }
            $feed->status = 'idle';
            $feed->save();

            return $inserted;
        } catch (\Throwable $e) {
            Log::warning('Feed fetch failed', [
                'feed_id' => $feed->id,
                'message' => $e->getMessage(),
            ]);

            $feed->update([
                'status' => 'error',
                'error_message' => $e->getMessage(),
                'last_fetched_at' => now(),
            ]);

            return 0;
        }
    }

    private function resolveParser(string $type): FeedParserInterface
    {
        return match ($type) {
            'json' => $this->jsonParser,
            default => $this->xmlParser,
        };
    }
}
