<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;
use Spatie\Tags\Tag as SpatieTag;

class Tag extends SpatieTag
{
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'type',
        'order_column',
    ];

    protected static function booted(): void
    {
        static::addGlobalScope('by-auth-user', function (Builder $builder): void {
            if (Auth::check()) {
                $builder->where('user_id', Auth::id());
            }
        });

        static::creating(function (self $model): void {
            if (Auth::check() && empty($model->user_id)) {
                $model->user_id = Auth::id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
