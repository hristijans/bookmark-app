<?php

namespace App\Actions\Tags;

use Spatie\Tags\Tag;

class DeleteTag
{
    public function execute(Tag $tag): void
    {
        $tag->delete();
    }
}
