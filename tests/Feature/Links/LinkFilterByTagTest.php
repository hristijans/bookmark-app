<?php

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('filters links by the tag query string and exposes activeTag', function () {
    $user = User::factory()->create();

    // Make links for this user
    $withPhp = Link::factory()->count(2)->create(['user_id' => $user->id]);
    foreach ($withPhp as $link) {
        $link->syncTags(['php']);
    }

    $withoutPhp = Link::factory()->count(2)->create(['user_id' => $user->id]);
    foreach ($withoutPhp as $link) {
        $link->syncTags(['javascript']);
    }

    $this->actingAs($user)
        ->get(route('links.index', ['tag' => 'php']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Links/Index')
            ->where('activeTag', 'php')
            ->has('links.data', 2)
            ->where('links.data.0.tags.0.name', fn ($name) => true) // presence
        );
});

it('preserves the tag query string in pagination links', function () {
    $user = User::factory()->create();

    // Create 12 links tagged php for this user to trigger pagination (per-page = 10)
    $phpLinks = Link::factory()->count(12)->create(['user_id' => $user->id]);
    foreach ($phpLinks as $link) {
        $link->syncTags(['php']);
    }

    $this->actingAs($user)
        ->get(route('links.index', ['tag' => 'php']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Links/Index')
            ->where('activeTag', 'php')
            ->where('links.per_page', 10)
            ->where('links.total', 12)
            ->where('links.next_page_url', fn ($url) => is_string($url) && str_contains($url, 'tag=php'))
        );
});
