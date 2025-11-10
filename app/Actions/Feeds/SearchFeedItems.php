<?php

namespace App\Actions\Feeds;

use App\Models\FeedItem;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class SearchFeedItems
{
    public function execute(array $data): LengthAwarePaginator
    {
        $perPage = (int) Arr::get($data, 'per_page', 10);
        $q = trim((string) ($data['q'] ?? ''));
        $source = trim((string) ($data['source'] ?? ''));
        $tags = [];
        if (! empty($data['tags']) && is_array($data['tags'])) {
            $tags = array_values(array_filter(array_map('strval', $data['tags'])));
        }

        if ($q !== '') {
            $builder = FeedItem::search($q)
                ->where('feed_id', '>=', 0) // no-op but allows additional wheres
                ->where('feed_items.user_id', (int) auth()->id())
                ->query(function ($eloquent) use ($source) {
                    $eloquent->with('feed')->latest('published_at');
                    if ($source !== '') {
                        $eloquent->whereHas('feed', function ($q) use ($source) {
                            $q->where('source', $source);
                        });
                    }
                });

            $results = $builder->paginate($perPage)->withQueryString();

            if (! empty($tags)) {
                $filtered = $results->getCollection()->filter(function ($item) use ($tags) {
                    $existing = collect($item->tags ?? [])->map('strval')->filter()->values()->all();

                    return ! empty(array_intersect($existing, $tags));
                })->values();
                $results->setCollection($filtered);
            }

            return $results;
        }

        $query = FeedItem::query()
            ->where('user_id', auth()->id())
            ->with('feed')
            ->latest('published_at');

        if ($source !== '') {
            $query->whereHas('feed', function ($q) use ($source) {
                $q->where('source', $source);
            });
        }

        if (! empty($tags)) {
            $query->where(function ($q) use ($tags) {
                foreach ($tags as $tag) {
                    $q->orWhereJsonContains('tags', $tag);
                }
            });
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
