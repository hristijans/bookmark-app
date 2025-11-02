<?php

namespace App\Http\Controllers\Link;

use App\Actions\Links\UpdateLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Link\UpdateLinkRequest;
use App\Models\Link;

class UpdateLinkController extends Controller
{
    public function __invoke(UpdateLinkRequest $request, Link $link, UpdateLink $updateLink)
    {
        $updateLink->execute($link, $request->validated());

        return redirect()->back()->with('success', 'Link updated successfully.');
    }
}
