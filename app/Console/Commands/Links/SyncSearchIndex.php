<?php

namespace App\Console\Commands\Links;

use App\Models\Link;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Laravel\Scout\EngineManager;

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
        $indexName = (string) $this->option('index') ?? 'links';

        /** @var \Meilisearch\Client $client */
        $client = app(EngineManager::class)->engine();

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
            'typoTolerance' => [
                'enabled' => true,
                'minWordSizeForTypos' => [
                    'oneTypo' => 5,
                    'twoTypos' => 9,
                ],
            ],
            'rankingRules' => [
                'words',
                'typo',
                'proximity',
                'attribute',
                'sort',
                'exactness',
            ],
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
