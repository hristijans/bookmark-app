<?php

namespace App\Http\Controllers\Feed;

use App\Actions\Feeds\CreateFeed;
use App\Http\Controllers\Controller;
use App\Http\Requests\Feed\StoreFeedRequest;
use Illuminate\Http\RedirectResponse;

class StoreFeedController extends Controller
{
    public function __construct(public CreateFeed $createFeed)
    {
    }

    public function __invoke(StoreFeedRequest $request): RedirectResponse
    {
        $feed = $this->createFeed->execute($request->validated());

        return redirect()->route('feeds.index')
            ->with('flash', ['message' => 'Feed added successfully.']);
    }
}
