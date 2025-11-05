<?php

namespace App\Http\Controllers\Tag;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Spatie\Tags\Tag;

class IndexTagController extends Controller
{
    public function __invoke()
    {
        $tags = Tag::query()->orderBy('name')->get(['id', 'name', 'slug']);

        return Inertia::render('Tags/Index', [
            'tags' => $tags,
        ]);
    }
}
