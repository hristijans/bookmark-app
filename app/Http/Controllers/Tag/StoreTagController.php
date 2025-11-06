<?php

namespace App\Http\Controllers\Tag;

use App\Actions\Tags\CreateTag;
use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreTagRequest;
use Illuminate\Http\RedirectResponse;

class StoreTagController extends Controller
{
    public function __invoke(StoreTagRequest $request, CreateTag $createTag): RedirectResponse
    {
        $createTag->execute($request->validated());

        return redirect()->back()->with('success', 'Tag saved.');
    }
}
