<?php

namespace App\Http\Controllers\Tag;

use App\Actions\Tags\SearchTag;
use App\Http\Controllers\Controller;
use Inertia\Inertia;

class IndexTagController extends Controller
{
    public function __invoke(SearchTag $searchTag)
    {
        $tags = $searchTag->execute();

        return Inertia::render('Tags/Index', [
            'tags' => $tags,
        ]);
    }
}
