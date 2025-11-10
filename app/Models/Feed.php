<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Feed extends Model
{
    /** @use HasFactory<\Database\Factories\FeedFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'type',
        'source',
        'last_fetched_at',
        'last_item_published_at',
        'status',
        'error_message',
    ];

    protected $casts = [
        'last_fetched_at' => 'datetime',
        'last_item_published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('by-auth-user', function ($builder): void {
            if (auth()->check()) {
                $builder->where('user_id', auth()->id());
            }
        });

        static::creating(function (self $model): void {
            if (auth()->check() && empty($model->user_id)) {
                $model->user_id = (int) auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(FeedItem::class);
    }
}
