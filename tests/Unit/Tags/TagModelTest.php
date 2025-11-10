<?php

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('applies the auth global scope to only return the authenticated user\'s tags', function (): void {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // Create tags for user A
    actingAs($userA);
    Tag::findOrCreate('php');
    Tag::findOrCreate('laravel');

    // Create tags for user B
    actingAs($userB);
    Tag::findOrCreate('javascript');

    // Assert user A only sees their tags
    actingAs($userA);
    $all = Tag::all();

    expect($all->pluck('name')->map(fn ($n) => is_array($n) ? ($n['en'] ?? reset($n)) : $n)->values())
        ->toContain('php', 'laravel')
        ->not->toContain('javascript');
});

it('sets user_id automatically on create when authenticated', function (): void {
    $user = User::factory()->create();

    actingAs($user);
    $tag = Tag::findOrCreate('frameworks');

    expect($tag->user_id)->toBe($user->id);
});

it('belongs to a user via the user relationship', function (): void {
    $user = User::factory()->create();

    actingAs($user);
    $tag = Tag::findOrCreate('testing');

    $tag->refresh();

    expect($tag->user)->not->toBeNull()
        ->and($tag->user->is($user))->toBeTrue();
});
