<?php

use App\Actions\Links\CreateThumbnail;
use App\Models\Link;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

it('stores a thumbnail using the screenshot service when configured', function () {
    Storage::fake('public');

    // Configure a fake screenshot service endpoint
    config()->set('services.link_thumbnail.screenshot_url_template', 'https://shot.example/api?url={url}');

    // Fake the screenshot HTTP call
    Http::fake([
        'shot.example/*' => Http::response('\x89PNG', 200, ['Content-Type' => 'image/png']),
    ]);

    $link = Link::factory()->create(['user_id' => \App\Models\User::factory()->create()->id]);

    // Execute the action directly (listener integration covered elsewhere)
    (new CreateThumbnail)->execute($link);

    $link->refresh();

    expect($link->thumbnail)->not->toBeEmpty();

    // The file should exist in the public disk under thumbnails/
    $files = Storage::disk('public')->allFiles('thumbnails');
    expect($files)->toHaveCount(1);
});

it('falls back to og:image when screenshot service is not configured', function () {
    Storage::fake('public');

    // Ensure no screenshot service
    config()->set('services.link_thumbnail.screenshot_url_template', null);

    // The target page returns an og:image meta pointing to a relative image
    $html = '<html><head><meta property="og:image" content="/images/cover.jpg"></head><body></body></html>';

    Http::fakeSequence()
        ->push($html, 200, ['Content-Type' => 'text/html']) // fetch page
        ->push('JPGDATA', 200, ['Content-Type' => 'image/jpeg']); // fetch image

    $link = Link::factory()->create([
        'user_id' => \App\Models\User::factory()->create()->id,
        'url' => 'https://example.com/docs',
    ]);

    (new CreateThumbnail)->execute($link);

    $link->refresh();
    expect($link->thumbnail)->not->toBeEmpty();

    $files = Storage::disk('public')->allFiles('thumbnails');
    expect($files)->toHaveCount(1);
});

it('gracefully does nothing (empty thumbnail) when all strategies fail', function () {
    Storage::fake('public');

    // Configure screenshot service but return non-image and then fail OG
    config()->set('services.link_thumbnail.screenshot_url_template', 'https://shot.example/api?url={url}');

    Http::fake([
        // First attempt (screenshot) returns non-image content type
        'shot.example/*' => Http::response('not image', 200, ['Content-Type' => 'text/plain']),
        // Any other HTTP request (page or image) fails
        '*' => Http::response('nope', 500),
    ]);

    $link = Link::factory()->create(['user_id' => \App\Models\User::factory()->create()->id]);

    (new CreateThumbnail)->execute($link);

    $link->refresh();
    expect($link->thumbnail)->toBe('');

    // No files written
    $files = Storage::disk('public')->allFiles('thumbnails');
    expect($files)->toBeEmpty();
});
