<?php

namespace App\Http\Controllers\Tag;

use App\Actions\Tags\UpdateTag;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\UpdateTagRequest;
use Illuminate\Http\RedirectResponse;
use Spatie\Tags\Tag;

class UpdateTagController extends Controller
{
    public function __invoke(UpdateTagRequest $request, Tag $tag, UpdateTag $updateTag): RedirectResponse
    {
        $tag = $updateTag->execute(tag: $tag, data: $request->validated());

        return redirect()->back()->with('success', 'Tag updated.');
    }
}
