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

        $link->tags()->attach($data['tags']);

        LinkCreated::dispatch($link);
    }
}
