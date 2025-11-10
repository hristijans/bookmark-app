<?php

namespace App\Actions\Feeds;

use App\Models\Feed;

class UpdateFeed
{
    public function execute(Feed $feed, array $data): Feed
    {
        $type = in_array(($data['type'] ?? $feed->type), ['xml', 'json'], true) ? $data['type'] : $feed->type;
        $url = trim((string) ($data['url'] ?? $feed->url));

        $feed->fill([
            'url' => $url,
            'type' => $type,
        ]);

        $feed->save();

        return $feed;
    }
}
