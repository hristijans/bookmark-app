<?php

namespace App\Http\Controllers\Tag;

use App\Actions\Tags\DeleteTag;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Spatie\Tags\Tag;

class DestroyTagController extends Controller
{
    public function __invoke(Tag $tag, DeleteTag $deleteTag): RedirectResponse
    {
        $deleteTag->execute($tag);

        return redirect()->back()->with('success', 'Tag deleted.');
    }
}
