<?php

namespace App\Actions\Tags;

use App\Models\Tag;

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
