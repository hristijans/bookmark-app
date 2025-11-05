<?php

namespace App\Http\Controllers\Tag;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tag\StoreTagRequest;
use Illuminate\Http\RedirectResponse;
use Spatie\Tags\Tag;

class StoreTagController extends Controller
{
    public function __invoke(StoreTagRequest $request): RedirectResponse
    {
        $name = $request->string('name')->toString();

        // Will create if not exists, otherwise return existing
        Tag::findOrCreate($name);

        return redirect()->back()->with('success', 'Tag saved.');
    }
}
