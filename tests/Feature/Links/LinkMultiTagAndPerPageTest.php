<?php

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('filters by ANY of multiple tags via tags[] and exposes activeTags', function () {
    $user = User::factory()->create();

    // Links with various tag combinations
    $phpOnly = Link::factory()->count(2)->create(['user_id' => $user->id]);
    foreach ($phpOnly as $l) {
        $l->syncTags(['php']);
    }

    $laravelOnly = Link::factory()->count(2)->create(['user_id' => $user->id]);
    foreach ($laravelOnly as $l) {
        $l->syncTags(['laravel']);
    }

    $both = Link::factory()->count(1)->create(['user_id' => $user->id]);
    foreach ($both as $l) {
        $l->syncTags(['php', 'laravel']);
    }

    $neither = Link::factory()->count(2)->create(['user_id' => $user->id]);
    foreach ($neither as $l) {
        $l->syncTags(['javascript']);
    }

    // Should match ANY (union): php OR laravel
    $this->actingAs($user)
        ->get(route('links.index', ['tags' => ['php', 'laravel']]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Links/Index')
            ->where('activeTags', ['php', 'laravel'])
            ->has('links.data', 5) // 2 phpOnly + 2 laravelOnly + 1 both = 5
        );
});

it('accepts legacy single tag ?tag=php alongside tags[]', function () {
    $user = User::factory()->create();

    $phpLinks = Link::factory()->count(3)->create(['user_id' => $user->id]);
    foreach ($phpLinks as $l) {
        $l->syncTags(['php']);
    }

    $otherLinks = Link::factory()->count(2)->create(['user_id' => $user->id]);
    foreach ($otherLinks as $l) {
        $l->syncTags(['go']);
    }

    $this->actingAs($user)
        ->get(route('links.index', ['tag' => 'php']))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Links/Index')
            ->where('activeTag', 'php')
            ->where('activeTags', ['php'])
            ->has('links.data', 3)
        );
});

it('supports per_page options and preserves query in pagination links', function () {
    $user = User::factory()->create();

    // Create 25 links tagged php to paginate
    $phpLinks = Link::factory()->count(25)->create(['user_id' => $user->id]);
    foreach ($phpLinks as $l) {
        $l->syncTags(['php']);
    }

    // Request 20 per page and filter by php
    $this->actingAs($user)
        ->get(route('links.index', ['tags' => ['php'], 'per_page' => 20]))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Links/Index')
            ->where('perPage', 20)
            ->where('links.per_page', 20)
            ->where('links.total', 25)
            ->where('links.next_page_url', function ($url) {
                return is_string($url)
                    && str_contains($url, 'tags%5B0%5D=php') // tags[0]=php urlencoded
                    && str_contains($url, 'per_page=20');
            })
        );
});
