<?php

namespace App\Actions\Links;

use App\Events\Link\LinkUpdated;
use App\Models\Link;

class UpdateLink
{
    public function execute(Link $link, array $data): void
    {
        $link->fill([
            'title' => $data['title'],
            'description' => $data['description'],
            'url' => $data['url'],
        ])->save();

        $link->refresh();

        // Sync tags by name using Spatie HasTags
        $tags = $data['tags'] ?? [];
        $link->syncTags($tags);

        LinkUpdated::dispatch($link);
    }
}
