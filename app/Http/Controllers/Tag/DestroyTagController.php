<?php

namespace App\Http\Controllers\Tag;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Spatie\Tags\Tag;

class DestroyTagController extends Controller
{
    public function __invoke(Tag $tag): RedirectResponse
    {
        $tag->delete();

        return redirect()->back()->with('success', 'Tag deleted.');
    }
}
