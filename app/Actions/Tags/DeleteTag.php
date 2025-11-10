<?php

namespace App\Actions\Tags;

use App\Models\Tag;

class DeleteTag
{
    public function execute(Tag $tag): void
    {
        $tag->delete();
    }
}
