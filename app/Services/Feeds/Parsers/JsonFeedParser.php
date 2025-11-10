<?php

namespace App\Services\Feeds\Parsers;

use App\Contracts\Feeds\FeedParserInterface;
use App\Data\Feeds\ParsedFeed;
use App\Data\Feeds\ParsedFeedItem;

/**
 * JSON Feed v1 parser: https://www.jsonfeed.org/version/1
 */
class JsonFeedParser implements FeedParserInterface
{
    public function parse(string $body, string $url): ParsedFeed
    {
        $data = json_decode($body, true);
        if (! is_array($data)) {
            throw new \InvalidArgumentException('Invalid JSON feed.');
        }

        $source = (string) ($data['title'] ?? parse_url($url, PHP_URL_HOST));
        $items = [];

        foreach (($data['items'] ?? []) as $item) {
            if (! is_array($item)) {
                continue;
            }

            $guid = (string) ($item['id'] ?? ($item['url'] ?? ''));
            $title = isset($item['title']) ? (string) $item['title'] : null;
            $link = isset($item['url']) ? (string) $item['url'] : null;
            $content = isset($item['content_html']) ? (string) $item['content_html'] : ((isset($item['content_text'])) ? (string) $item['content_text'] : null);
            $summary = isset($item['summary']) ? (string) $item['summary'] : null;
            $author = null;
            if (isset($item['author']['name'])) {
                $author = (string) $item['author']['name'];
            } elseif (isset($data['author']['name'])) {
                $author = (string) $data['author']['name'];
            }

            $tags = [];
            if (! empty($item['tags']) && is_array($item['tags'])) {
                $tags = array_values(array_filter(array_map('strval', $item['tags'])));
            }

            $publishedAt = null;
            if (! empty($item['date_published'])) {
                $ts = strtotime((string) $item['date_published']);
                if ($ts) {
                    $publishedAt = (new \DateTimeImmutable())->setTimestamp($ts);
                }
            }

            $items[] = new ParsedFeedItem(
                guid: $guid ?: ($link ?: null),
                title: $title,
                url: $link,
                content: $content,
                summary: $summary,
                author: $author,
                tags: $tags,
                publishedAt: $publishedAt,
            );
        }

        return new ParsedFeed(
            source: $source ?: (string) parse_url($url, PHP_URL_HOST),
            items: $items,
        );
    }
}
