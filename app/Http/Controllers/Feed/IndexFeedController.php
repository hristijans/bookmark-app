<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use App\Models\Feed;
use Inertia\Inertia;

class IndexFeedController extends Controller
{
    public function __invoke()
    {
        $feeds = Feed::query()
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Feeds/Index', [
            'feeds' => $feeds,
        ]);
    }
}
