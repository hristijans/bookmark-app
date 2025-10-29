<?php

namespace App\Http\Controllers\Link;

use App\Http\Controllers\Controller;
use App\Http\Requests\Link\CreateLinkRequest;
use App\Http\Requests\Link\UpdateLinkRequest;
use App\Models\Link;

class UpdateLinkController extends Controller
{
    public function __invoke(UpdateLinkRequest $request, Link $link)
    {
        $link->tap(function ($link) use ($request) {
            $link->update($request->validated());
        });

        return redirect()->back()->with('success', 'Link updated successfully.');
    }
}
