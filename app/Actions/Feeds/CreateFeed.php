<?php

namespace App\Actions\Feeds;

use App\Models\Feed;

class CreateFeed
{
    public function execute(array $data): Feed
    {
        // Normalize
        $type = in_array(($data['type'] ?? 'xml'), ['xml', 'json'], true) ? $data['type'] : 'xml';
        $url = trim((string) $data['url']);

        $feed = new Feed([
            'user_id' => (int) auth()->id(),
            'url' => $url,
            'type' => $type,
            'status' => 'idle',
        ]);

        $feed->save();

        return $feed;
    }
}
