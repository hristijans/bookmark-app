<?php

namespace App\Actions\Links;

use App\Models\Link;

class SearchLink
{
    public function execute(array $data)
    {
        $query = Link::query()
            ->with('tags')
            ->orderBy('created_at', 'desc');

        return $query->paginate(10)->withQueryString();
    }
}
