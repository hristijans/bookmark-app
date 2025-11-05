<?php

namespace App\Actions\Links;

use App\Events\Link\LinkCreated;
use App\Models\Link;

class CreateLink
{
    public function execute(array $data): void
    {
        $link = Link::create([
            'user_id' => auth()->id(),
            'url' => $data['url'],
            'title' => $data['title'],
            'description' => $data['description'],
        ]);

        // Sync tags by name using Spatie HasTags. If none provided, ensure no tags are attached.
        $tags = $data['tags'] ?? [];
        $link->syncTags($tags);

        LinkCreated::dispatch($link);
    }
}
