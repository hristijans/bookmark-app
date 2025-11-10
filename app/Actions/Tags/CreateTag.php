<?php

namespace App\Actions\Tags;

use App\Models\Tag;

class CreateTag
{
    public function execute(array $data): Tag
    {
        return Tag::findOrCreate($data['name']);
    }
}
