<?php

namespace App\Http\Controllers\Tag;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\UpdateTagRequest;
use Illuminate\Http\RedirectResponse;
use Spatie\Tags\Tag;

class UpdateTagController extends Controller
{
    public function __invoke(UpdateTagRequest $request, Tag $tag): RedirectResponse
    {
        $tag->name = $request->string('name')->toString();
        $tag->save();

        return redirect()->back()->with('success', 'Tag updated.');
    }
}
