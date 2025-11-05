<?php

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('creates a link with tags and persists them', function () {
    $user = User::factory()->create();

    $payload = [
        'url' => 'https://laravel.com',
        'title' => 'Laravel',
        'description' => 'PHP framework',
        'tags' => ['php', 'framework'],
    ];

    $response = $this->actingAs($user)->post('/links', $payload);

    $response->assertRedirect();

    $link = Link::first();

    expect($link)->not->toBeNull();
    expect($link->hasTag('php'))->toBeTrue();
    expect($link->hasTag('framework'))->toBeTrue();
});

it('updates a link tags (sync add and remove)', function () {
    $user = User::factory()->create();

    $link = Link::factory()->create([
        'user_id' => $user->id,
    ]);

    // Seed initial tags
    $link->syncTags(['one', 'two']);

    $payload = [
        'url' => $link->url,
        'title' => 'Updated',
        'description' => 'Updated desc',
        'tags' => ['two', 'three'],
    ];

    $response = $this->actingAs($user)->put("/links/{$link->id}", $payload);

    $response->assertRedirect();

    $link->refresh();

    expect($link->hasTag('one'))->toBeFalse();
    expect($link->hasTag('two'))->toBeTrue();
    expect($link->hasTag('three'))->toBeTrue();
});
