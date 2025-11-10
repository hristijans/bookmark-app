<?php

namespace App\Data\Feeds;

use Carbon\CarbonInterface;

class ParsedFeedItem
{
    /**
     * @param array<int, string> $tags
     */
    public function __construct(
        public ?string $guid,
        public ?string $title,
        public ?string $url,
        public ?string $content,
        public ?string $summary,
        public ?string $author,
        public array $tags,
        public ?\DateTimeInterface $publishedAt,
    ) {
    }
}
