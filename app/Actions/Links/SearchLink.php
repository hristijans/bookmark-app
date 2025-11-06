<?php

namespace App\Actions\Links;

use App\Models\Link;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;

class SearchLink
{
    public function execute(array $data): LengthAwarePaginator
    {

        $perPage = Arr::get($data, 'per_page', 10);

        // Normalize tags input: support legacy single 'tag' and new 'tags' array
        $tags = [];
        if (! empty($data['tags']) && is_array($data['tags'])) {
            $tags = array_values(array_filter(array_map('strval', $data['tags'])));
        } elseif (! empty($data['tag']) && is_string($data['tag'])) {
            $tags = [(string) $data['tag']];
        }

        $q = trim((string) ($data['q'] ?? ''));

        // When a free-text query is present, prefer Scout full-text search
        if ($q !== '') {
            $builder = Link::search($q)
                // Always scope to the authenticated user via a filterable attribute
                ->where('user_id', (int) auth()->id())
                // Eager-load tags for rendering
                ->query(fn ($eloquent) => $eloquent->with('tags')->orderBy('created_at', 'desc'));

            // Meilisearch's Scout where() combines with AND; to achieve ANY tag match we will filter
            // the paginated results collection at the application layer when tags are provided.
            $results = $builder->paginate($perPage)->withQueryString();

            if (! empty($tags)) {
                $filtered = $results->getCollection()->filter(function ($link) use ($tags) {
                    $existing = collect($link->tags ?? [])->map(function ($tag) {
                        $name = $tag->name ?? null;
                        if (is_array($name)) {
                            $first = reset($name);
                            return is_string($first) ? $first : '';
                        }
                        return (string) $name;
                    })->filter()->values()->all();

                    return ! empty(array_intersect($existing, $tags));
                })->values();

                // Replace the collection but keep meta
                $results->setCollection($filtered);
            }

            return $results;
        }

        // Fallback to Eloquent listing with optional tag filtering
        $query = Link::query()
            ->with('tags')
            ->orderBy('created_at', 'desc');

        if (! empty($tags)) {
            $query->withAnyTags($tags);
        }

        return $query->paginate($perPage)->withQueryString();
    }
}
