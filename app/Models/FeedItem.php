<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class FeedItem extends Model
{
    /** @use HasFactory<\Database\Factories\FeedItemFactory> */
    use HasFactory, Searchable;

    protected $fillable = [
        'feed_id',
        'guid',
        'title',
        'url',
        'content',
        'summary',
        'author',
        'tags',
        'published_at',
        'fetched_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'published_at' => 'datetime',
        'fetched_at' => 'datetime',
    ];

    public function feed(): BelongsTo
    {
        return $this->belongsTo(Feed::class);
    }

    public function searchableAs(): string
    {
        return 'feed_items';
    }

    public function toSearchableArray(): array
    {
        $this->loadMissing('feed');

        return [
            'id' => (int) $this->id,
            'feed_id' => (int) $this->feed_id,
            'source' => (string) ($this->feed->source ?? ''),
            'title' => (string) ($this->title ?? ''),
            'url' => (string) ($this->url ?? ''),
            'content' => (string) ($this->content ?? ''),
            'summary' => (string) ($this->summary ?? ''),
            'author' => (string) ($this->author ?? ''),
            'tags' => $this->tags ?? [],
            'published_at' => optional($this->published_at)->toIso8601String(),
            'fetched_at' => optional($this->fetched_at)->toIso8601String(),
            'created_at' => optional($this->created_at)->toIso8601String(),
        ];
    }
}
