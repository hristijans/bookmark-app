<?php

namespace App\Console\Commands\Feeds;

use App\Models\Feed;
use App\Services\Feeds\FeedFetcher;
use Illuminate\Console\Command;

class SyncFeeds extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'feeds:sync {--feed= : Only sync a specific feed id} {--user= : Only sync feeds for a given user id}';

    /**
     * The console command description.
     */
    protected $description = 'Fetch and parse all configured feeds and persist any new items.';

    public function __construct(public FeedFetcher $fetcher)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $feedId = $this->option('feed');
        $userId = $this->option('user');

        $query = Feed::query()->withoutGlobalScopes();

        if (! empty($feedId)) {
            $query->where('id', (int) $feedId);
        }

        if (! empty($userId)) {
            $query->where('user_id', (int) $userId);
        }

        $count = 0;
        $inserted = 0;

        $query->orderBy('id')->chunkById(100, function ($feeds) use (&$count, &$inserted): void {
            foreach ($feeds as $feed) {
                $count++;
                $this->info("Syncing feed #{$feed->id} ({$feed->url}) ...");
                $created = $this->fetcher->fetch($feed);
                $inserted += $created;
                $this->line("→ added {$created} new item(s)");
            }
        });

        $this->info("Completed. Processed {$count} feed(s); inserted {$inserted} new item(s).");

        return self::SUCCESS;
    }
}
