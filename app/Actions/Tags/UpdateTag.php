<?php

namespace App\Actions\Tags;

use Spatie\Tags\Tag;

class UpdateTag
{
    public function execute(Tag $tag, array $data): Tag
    {
        $tag->name = $data['name'];
        $tag->save();
        $tag->refresh();

        return $tag;
    }
}
