<?php

namespace App\Services\Feeds\Parsers;

use App\Contracts\Feeds\FeedParserInterface;
use App\Data\Feeds\ParsedFeed;
use App\Data\Feeds\ParsedFeedItem;

class XmlFeedParser implements FeedParserInterface
{
    public function parse(string $body, string $url): ParsedFeed
    {
        $xml = @simplexml_load_string($body, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($xml === false) {
            // Try to fetch as Atom even if malformed
            throw new \InvalidArgumentException('Invalid XML feed.');
        }

        $namespaces = $xml->getNamespaces(true);

        // Detect RSS vs Atom
        if (isset($xml->channel)) {
            $channel = $xml->channel;
            $source = (string) ($channel->title ?? parse_url($url, PHP_URL_HOST));
            $items = [];

            foreach ($channel->item as $item) {
                $guid = (string) ($item->guid ?? '');
                $title = (string) ($item->title ?? '');
                $link = (string) ($item->link ?? '');

                $content = '';
                if (isset($item->children($namespaces['content'] ?? null)->encoded)) {
                    $content = (string) $item->children($namespaces['content'])->encoded;
                } elseif (isset($item->description)) {
                    $content = (string) $item->description;
                }

                $summary = (string) ($item->description ?? '');
                $author = (string) ($item->author ?? '');

                // Tags / categories
                $tags = [];
                if (isset($item->category)) {
                    foreach ($item->category as $cat) {
                        $tags[] = trim((string) $cat);
                    }
                }

                $publishedAt = null;
                if (isset($item->pubDate)) {
                    $ts = strtotime((string) $item->pubDate);
                    if ($ts) {
                        $publishedAt = (new \DateTimeImmutable())->setTimestamp($ts);
                    }
                }

                $items[] = new ParsedFeedItem(
                    guid: $guid ?: ($link ?: null),
                    title: $title ?: null,
                    url: $link ?: null,
                    content: $content ?: null,
                    summary: $summary ?: null,
                    author: $author ?: null,
                    tags: array_values(array_filter(array_unique($tags))),
                    publishedAt: $publishedAt,
                );
            }

            return new ParsedFeed(
                source: $source ?: (string) parse_url($url, PHP_URL_HOST),
                items: $items,
            );
        }

        // Atom
        $source = (string) ($xml->title ?? parse_url($url, PHP_URL_HOST));
        $items = [];
        foreach ($xml->entry as $entry) {
            $guid = (string) ($entry->id ?? '');
            $title = (string) ($entry->title ?? '');

            $link = '';
            foreach ($entry->link as $lnk) {
                $attrs = $lnk->attributes();
                if ((string) ($attrs['rel'] ?? 'alternate') === 'alternate' && isset($attrs['href'])) {
                    $link = (string) $attrs['href'];
                    break;
                }
            }

            $content = (string) ($entry->content ?? '');
            $summary = (string) ($entry->summary ?? '');
            $author = '';
            if (isset($entry->author->name)) {
                $author = (string) $entry->author->name;
            }

            $tags = [];
            foreach ($entry->category as $cat) {
                $attrs = $cat->attributes();
                if (isset($attrs['term'])) {
                    $tags[] = (string) $attrs['term'];
                }
            }

            $publishedAt = null;
            $dateStr = (string) ($entry->updated ?? $entry->published ?? '');
            if ($dateStr) {
                $ts = strtotime($dateStr);
                if ($ts) {
                    $publishedAt = (new \DateTimeImmutable())->setTimestamp($ts);
                }
            }

            $items[] = new ParsedFeedItem(
                guid: $guid ?: ($link ?: null),
                title: $title ?: null,
                url: $link ?: null,
                content: $content ?: null,
                summary: $summary ?: null,
                author: $author ?: null,
                tags: array_values(array_filter(array_unique($tags))),
                publishedAt: $publishedAt,
            );
        }

        return new ParsedFeed(
            source: $source ?: (string) parse_url($url, PHP_URL_HOST),
            items: $items,
        );
    }
}
