<?php

namespace App\Http\Controllers\Tag;

use App\Http\Controllers\Controller;
use Spatie\Tags\Tag;

class SearchTagsController extends Controller
{
    public function __invoke()
    {
        return Tag::all();
    }
}
