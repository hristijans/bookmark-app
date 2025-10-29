<?php

namespace App\Http\Controllers\Link;

use App\Http\Controllers\Controller;
use App\Http\Requests\Link\CreateLinkRequest;
use App\Models\Link;

class CreateLinkController extends Controller
{
    public function __invoke(CreateLinkRequest $request)
    {
        Link::create($request->validated());

        return redirect()->back()->with('success', 'Link created successfully.');
    }
}
