<?php

namespace App\Actions\Links;

use App\Models\Link;

class CreateThumbnail
{
    public function execute(Link $link): void
    {
        $thumbnail = $this->getThumbnail($link->url);

        $link->update([
            'thumbnail' => $thumbnail,
        ]);
    }

    private function getThumbnail(string $url): string
    {

    }
}
