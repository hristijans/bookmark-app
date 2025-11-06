<?php

namespace App\Actions\Tags;

use Spatie\Tags\Tag;

class CreateTag
{
    public function execute(array $data): Tag
    {
        return Tag::findOrCreate($data['name']);
    }
}
