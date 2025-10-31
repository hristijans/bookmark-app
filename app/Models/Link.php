<?php

namespace App\Models;

use App\Models\Scopes\Link\UserScope;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Tags\HasTags;

class Link extends Model
{
    /** @use HasFactory<\Database\Factories\LinkFactory> */
    use HasFactory, HasTags, SoftDeletes;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'url',
        'is_private',
        'is_active',
    ];

    protected static function boot(): void
    {
        parent::boot();
        static::addGlobalScope(UserScope::class);
    }
}
