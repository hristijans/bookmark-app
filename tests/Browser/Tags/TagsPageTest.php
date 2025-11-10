<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('renders the Tags page in the browser without JS errors', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user);

    $page = visit('/tags');

    $page->assertSee('Tags')
        ->assertNoJavascriptErrors();
});
