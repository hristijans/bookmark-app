<?php

namespace App\Http\Controllers\Link;

use App\Actions\Links\CreateLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Link\CreateLinkRequest;

class CreateLinkController extends Controller
{
    public function __invoke(CreateLinkRequest $request, CreateLink $createLink)
    {
        $createLink->execute($request->validated());

        return redirect()->back()->with('success', 'Link created successfully.');
    }
}
