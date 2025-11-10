<?php

namespace App\Http\Controllers\Feed;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class CreateFeedController extends Controller
{
    public function __invoke(): \Inertia\Response
    {
        return Inertia::render('Feeds/Create');
    }
}
