<?php

namespace App\Console\Commands\Links;

use App\Models\Link;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SyncSearchIndex extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'links:scout-sync {--fresh : Recreate the Meilisearch index and re-import all records} {--chunk=1000 : Chunk size when importing} {--index=links : Index name to configure}';

    /**
     * The console command description.
     */
    protected $description = 'Configure the Meilisearch index for links and synchronize all records.';

    public function handle(): int
    {
        $driver = config('scout.driver');
        $indexName = (string) $this->option('index');

        if ($driver !== 'meilisearch') {
            $this->warn("SCOUT_DRIVER is '{$driver}'. Skipping Meilisearch-specific configuration. Running a generic scout:import instead.");

            Artisan::call('scout:import', ['model' => Link::class]);
            $this->info(trim(Artisan::output()));

            return self::SUCCESS;
        }

        /** @var \Meilisearch\Client $client */
        $client = app('scout.meilisearch.client');

        if ($this->option('fresh')) {
            try {
                $client->deleteIndex($indexName);
                $this->info("Deleted index '{$indexName}'.");
            } catch (\Throwable $e) {
                // ignore if not existing
            }
        }

        // Ensure the index exists
        try {
            $index = $client->getIndex($indexName);
        } catch (\Throwable $e) {
            $index = $client->createIndex($indexName, ['primaryKey' => 'id']);
            $this->info("Created index '{$indexName}'.");
        }

        // Configure index settings
        $client->index($indexName)->updateSettings([
            'searchableAttributes' => ['title', 'url', 'description', 'tags'],
            'filterableAttributes' => ['user_id', 'tags', 'created_at'],
            'sortableAttributes' => ['created_at'],
        ]);
        $this->info('Updated index settings.');

        // Import all Links into the index. Use the official Laravel command for reliability.
        $chunk = max(100, (int) $this->option('chunk'));
        config(['scout.chunk.searchable' => $chunk]);

        // Import WITHOUT the user global scope so all users get indexed
        Link::withoutGlobalScopes()->whereNull('deleted_at')->orderBy('id')->chunkById($chunk, function ($links) {
            // Sync tags relation for correct toSearchableArray payload
            $links->load('tags');
            $links->each->searchable();
        });

        $this->info('Links synchronized to Meilisearch.');

        return self::SUCCESS;
    }
}
