<?php

namespace App\Http\Controllers\Link;

use App\Actions\Links\SearchLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Link\SearchLinkRequest;
use Inertia\Inertia;

class IndexLinkController extends Controller
{
    public function __invoke(SearchLinkRequest $request, SearchLink $searchLink)
    {
        $links = $searchLink->execute($request->validated());

        return Inertia::render('Links/Index', [
            'links' => $links,
        ]);
    }
}
