<?php

namespace App\Models;

use App\Models\Scopes\Link\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Laravel\Scout\Searchable;
use Spatie\Tags\HasTags;

class Link extends Model
{
    /** @use HasFactory<\Database\Factories\LinkFactory> */
    use HasFactory, HasTags, Searchable, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'url',
        'thumbnail',
        'is_private',
        'is_active',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::addGlobalScope(UserScope::class);

        // Keep search index in sync with soft delete lifecycle
        static::deleted(function (self $link): void {
            // For soft-deletes, ensure the record is removed from the index
            $link->unsearchable();
        });

        static::restored(function (self $link): void {
            $link->searchable();
        });
    }

    public function searchableAs(): string
    {
        return 'links';
    }

    public function toSearchableArray(): array
    {
        // Ensure tags are loaded minimally
        $this->loadMissing('tags');

        // Extract tag names as simple strings regardless of locale format
        $tagNames = $this->tags->pluck('name')->map(function ($name) {
            if (is_array($name)) {
                $first = reset($name);

                return is_string($first) ? $first : '';
            }

            return (string) $name;
        })->filter()->values()->all();

        return [
            'id' => (int) $this->id,
            'user_id' => (int) $this->user_id,
            'title' => (string) ($this->title ?? ''),
            'url' => (string) ($this->url ?? ''),
            'description' => (string) ($this->description ?? ''),
            'tags' => $tagNames,
            'created_at' => optional($this->created_at)->toIso8601String(),
            'updated_at' => optional($this->updated_at)->toIso8601String(),
        ];
    }

    public function shouldBeSearchable(): bool
    {
        // Exclude soft-deleted and optionally inactive/private items if desired
        if ($this->trashed()) {
            return false;
        }

        return true;
    }

    public function searchableOptions(): array
    {
        return [
            'filterableAttributes' => ['status', 'category_id', 'created_at'],
            'sortableAttributes' => ['created_at', 'updated_at', 'title'],
            'rankingRules' => [
                'words',
                'typo',
                'proximity',
                'attribute',
                'sort',
                'exactness',
                'created_at:desc',
            ],
            'searchableAttributes' => [
                'title',
                'description',
                'tags',
            ],
            'displayedAttributes' => ['*'],
            'stopWords' => ['the', 'a', 'an'],
            'synonyms' => [
                'football' => ['soccer'],
                'fixture' => ['match', 'game'],
            ],
            'distinctAttribute' => 'product_id',
            'typoTolerance' => [
                'enabled' => true,
                'minWordSizeForTypos' => [
                    'oneTypo' => 5,
                    'twoTypos' => 9,
                ],
                'disableOnWords' => [],
                'disableOnAttributes' => [],
            ],
            'faceting' => [
                'maxValuesPerFacet' => 100,
            ],
            'pagination' => [
                'maxTotalHits' => 1000,
            ],
        ];
    }
}
