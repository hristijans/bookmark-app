<?php

namespace App\Listeners\Link;

use App\Actions\Links\CreateThumbnail;
use App\Events\Link\LinkCreated;
use App\Events\Link\LinkUpdated;
use Illuminate\Support\Facades\Log;

class CreateLinkThumbnail
{
    /**
     * Create the event listener.
     */
    public function __construct(public CreateThumbnail $createThumbnail)
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(LinkCreated|LinkUpdated $event): void
    {
        try {
            $this->createThumbnail->execute($event->link);
        } catch (\Throwable $th) {
            Log::error('Error while creating link thumbnail: ', [
                'exception' => $th->getMessage(),
            ]);
        }
    }
}
