<?php

namespace App\Http\Controllers\Link;

use App\Actions\Links\SearchLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Link\SearchLinkRequest;
use App\Models\Tag;
use Inertia\Inertia;

class IndexLinkController extends Controller
{
    public function __invoke(SearchLinkRequest $request, SearchLink $searchLink)
    {
        $validated = $request->validated();
        $links = $searchLink->execute($validated);

        $tagNames = Tag::query()
            ->when(auth()->check(), fn ($q) => $q->where('user_id', auth()->id()))
            ->get()
            ->pluck('name')
            ->map(function ($name) {
                if (is_array($name)) {
                    $first = reset($name);

                    return is_string($first) ? $first : '';
                }

                return (string) $name;
            })
            ->filter()
            ->sort()
            ->values()
            ->all();

        // Active tags: prefer tags[] but support legacy tag
        $activeTags = [];
        if (! empty($validated['tags']) && is_array($validated['tags'])) {
            $activeTags = array_values(array_filter(array_map('strval', $validated['tags'])));
        } elseif (! empty($validated['tag']) && is_string($validated['tag'])) {
            $activeTags = [(string) $validated['tag']];
        }

        $perPage = isset($validated['per_page']) ? (int) $validated['per_page'] : 10;
        if (! in_array($perPage, [10, 20, 50, 100], true)) {
            $perPage = 10;
        }

        return Inertia::render('Links/Index', [
            'links' => $links,
            'availableTags' => $tagNames,
            // Legacy single activeTag kept for backward compatibility with UI
            'activeTag' => (string) ($activeTags[0] ?? ''),
            'activeTags' => $activeTags,
            'q' => (string) ($validated['q'] ?? ''),
            'perPage' => $perPage,
            'perPageOptions' => [10, 20, 50, 100],
        ]);
    }
}
