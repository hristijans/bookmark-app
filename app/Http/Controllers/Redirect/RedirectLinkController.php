<?php

namespace App\Http\Controllers\Redirect;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Illuminate\Http\Request;

class RedirectLinkController extends Controller
{
    public function __invoke(Request $request, Link $link)
    {
        return redirect()->to($link->url);
    }
}
