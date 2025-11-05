<?php

namespace App\Http\Controllers\Link;

use App\Actions\Links\SearchLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Link\SearchLinkRequest;
use Inertia\Inertia;
use Spatie\Tags\Tag;

class IndexLinkController extends Controller
{
    public function __invoke(SearchLinkRequest $request, SearchLink $searchLink)
    {
        $links = $searchLink->execute($request->validated());

        $tagNames = Tag::query()->get()->pluck('name')->map(function ($name) {
            if (is_array($name)) {
                $first = reset($name);

                return is_string($first) ? $first : '';
            }

            return (string) $name;
        })->filter()->sort()->values()->all();

        return Inertia::render('Links/Index', [
            'links' => $links,
            'availableTags' => $tagNames,
        ]);
    }
}
