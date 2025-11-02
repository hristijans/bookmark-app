<?php

namespace App\Actions\Links;

use App\Events\Link\LinkUpdated;
use App\Models\Link;

class UpdateLink
{
    public function execute(Link $link, array $data)
    {
        $link->fill([
            'title' => $data['title'],
            'description' => $data['description'],
            'url' => $data['url'],
        ])->save();

        $link->refresh();

        $link->tags()->sync($data['tags']);

        LinkUpdated::dispatch($link);
    }
}
