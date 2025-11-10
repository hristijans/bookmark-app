<?php

use App\Models\Tag as AppTag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

// Helpers
function tagNameString($name): string {
    return is_array($name) ? ($name['en'] ?? reset($name)) : (string) $name;
}

it('requires authentication for all tag routes', function (): void {
    // Index
    $this->get(route('tags.index'))->assertRedirectToRoute('login');

    // Store
    $this->post(route('tags.store'), ['name' => 'php'])->assertRedirectToRoute('login');

    // Update
    $this->put(route('tags.update', ['tag' => 1]), ['name' => 'new'])
        ->assertRedirectToRoute('login');

    // Destroy
    $this->delete(route('tags.destroy', ['tag' => 1]))
        ->assertRedirectToRoute('login');
});

it('renders the tags index with Inertia and returns user\'s tags ordered by name', function (): void {
    $user = User::factory()->create();
    $other = User::factory()->create();

    // Seed some tags for both users
    actingAs($user);
    AppTag::findOrCreate('laravel');
    AppTag::findOrCreate('php');

    actingAs($other);
    AppTag::findOrCreate('javascript');

    // Back as main user
    actingAs($user);

    $this->get(route('tags.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tags/Index')
            ->has('tags', fn (Assert $tags) => $tags
                ->etc()
            )
        );

    $names = AppTag::all()->pluck('name')->map(fn ($n) => tagNameString($n))->values()->all();
    $sorted = $names; sort($sorted, SORT_NATURAL | SORT_FLAG_CASE);

    expect($names)->toEqual($sorted)
        ->and($names)->toContain('laravel', 'php')
        ->and($names)->not->toContain('javascript');
});

it('creates a tag via POST /tags and does not duplicate when posting the same name again', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $response = $this->post(route('tags.store'), ['name' => 'framework']);
    $response->assertRedirect();

    $response = $this->post(route('tags.store'), ['name' => 'framework']);
    $response->assertRedirect();

    $tags = AppTag::all();

    expect($tags)->toHaveCount(1)
        ->and(tagNameString($tags->first()->name))->toBe('framework')
        ->and($tags->first()->user_id)->toBe($user->id);
});

it('validates the store request name field', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $this->post(route('tags.store'), ['name' => ''])
        ->assertSessionHasErrors(['name']);
});

it('updates a tag name via PUT /tags/{tag}', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $tag = AppTag::findOrCreate('old');

    $this->put(route('tags.update', ['tag' => $tag->id]), ['name' => 'new'])
        ->assertRedirect();

    $tag->refresh();

    expect(tagNameString($tag->name))->toBe('new');
});

it('validates the update request name field', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $tag = AppTag::findOrCreate('valid');

    $this->put(route('tags.update', ['tag' => $tag->id]), ['name' => ''])
        ->assertSessionHasErrors(['name']);
});

it('deletes a tag via DELETE /tags/{tag}', function (): void {
    $user = User::factory()->create();
    actingAs($user);

    $tag = AppTag::findOrCreate('to-delete');

    $this->delete(route('tags.destroy', ['tag' => $tag->id]))
        ->assertRedirect();

    // Using withoutGlobalScopes to ensure it is truly deleted and not just filtered
    $exists = AppTag::withoutGlobalScopes()->whereKey($tag->id)->exists();

    expect($exists)->toBeFalse();
});
