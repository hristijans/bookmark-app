<?php

namespace App\Http\Controllers\Link;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;

class CreateLinkController extends Controller
{
    public function __invoke(Request $request)
    {

        Link::create(array_merge($request->all(), ['user_id' => $request->user()->id]));

        return redirect()->back();
    }
}
