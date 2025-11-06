<?php

use App\Models\Link;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('shows the correct per-tag link counts for the authenticated user', function () {
    $userA = User::factory()->create();
    $userB = User::factory()->create();

    // User A links
    $aLinksPhp = Link::factory()->count(2)->create(['user_id' => $userA->id]);
    foreach ($aLinksPhp as $link) {
        $link->syncTags(['php']);
    }

    $aLinksJs = Link::factory()->count(1)->create(['user_id' => $userA->id]);
    foreach ($aLinksJs as $link) {
        $link->syncTags(['javascript']);
    }

    // One soft-deleted link for user A with tag 'php' should NOT be counted
    $deleted = Link::factory()->create(['user_id' => $userA->id]);
    $deleted->syncTags(['php']);
    $deleted->delete();

    // User B links with same tag should NOT be counted for user A
    $bLinksPhp = Link::factory()->count(3)->create(['user_id' => $userB->id]);
    foreach ($bLinksPhp as $link) {
        $link->syncTags(['php']);
    }

    $this->actingAs($userA)
        ->get(route('tags.index'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('Tags/Index')
            ->has('tags', fn (Assert $tags) => $tags
                // Expect at least the two tags we used
                ->where('0.id', fn ($id) => is_int($id)) // smoke check item shape
                ->where('0.name', fn ($name) => is_array($name) || is_string($name))
                // Verify counts by finding the tag entries regardless of order
                ->where('tags', function ($all) {
                    // Convert to simple array for scanning
                    $php = collect($all)->first(function ($t) {
                        return ($t['name']['en'] ?? $t['name'] ?? '') === 'php';
                    });
                    $js = collect($all)->first(function ($t) {
                        return ($t['name']['en'] ?? $t['name'] ?? '') === 'javascript';
                    });

                    expect($php)->not->toBeNull();
                    expect($php['links_count'] ?? null)->toBe(2);

                    expect($js)->not->toBeNull();
                    expect($js['links_count'] ?? null)->toBe(1);

                    return true;
                })
            )
        );
});
