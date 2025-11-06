<?php

namespace App\Listeners\Link;

use App\Actions\Links\CreateThumbnail;
use App\Events\Link\LinkCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class CreateLinkThumbnail
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LinkCreated $event, CreateThumbnail $createThumbnail): void
    {
        try {
            $createThumbnail->execute($event->link);
        } catch (\Throwable $th) {
            Log::error('Error while creating link thumbnail: ', [
                'exception' => $th->getMessage(),
            ]);
        }
    }
}
