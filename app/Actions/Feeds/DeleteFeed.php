<?php

namespace App\Actions\Feeds;

use App\Models\Feed;

class DeleteFeed
{
    public function execute(Feed $feed): void
    {
        $feed->delete();
    }
}
