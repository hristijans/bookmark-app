<?php

namespace App\Http\Controllers\Link;

use App\Http\Controllers\Controller;
use App\Models\Link;
use Inertia\Inertia;

class IndexLinkController extends Controller
{
    public function __invoke()
    {
        $links = Link::latest()->paginate(10)->withQueryString();

        return Inertia::render('Links/Index', [
            'links' => $links,
        ]);
    }
}
