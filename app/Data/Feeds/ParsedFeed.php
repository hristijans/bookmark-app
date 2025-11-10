<?php

namespace App\Data\Feeds;

class ParsedFeed
{
    /** @param array<int, ParsedFeedItem> $items */
    public function __construct(
        public string $source,
        public array $items,
    ) {
    }
}
