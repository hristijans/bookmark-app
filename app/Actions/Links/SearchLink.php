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

        // Normalize tags input: support legacy single 'tag' and new 'tags' array
        $tags = [];
        if (!empty($data['tags']) && is_array($data['tags'])) {
            $tags = array_values(array_filter(array_map('strval', $data['tags'])));
        } elseif (!empty($data['tag']) && is_string($data['tag'])) {
            $tags = [(string) $data['tag']];
        }

        if (!empty($tags)) {
            // ANY match (OR) per requirements
            $query->withAnyTags($tags);
        }

        $perPage = 10;
        if (isset($data['per_page']) && in_array((int) $data['per_page'], [10, 20, 50, 100], true)) {
            $perPage = (int) $data['per_page'];
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
