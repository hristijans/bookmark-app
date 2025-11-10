<?php

namespace App\Contracts\Feeds;

use App\Data\Feeds\ParsedFeed;

interface FeedParserInterface
{
    /**
     * Parse raw feed content into a normalized ParsedFeed DTO.
     */
    public function parse(string $body, string $url): ParsedFeed;
}
