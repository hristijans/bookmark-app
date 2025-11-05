<?php

use App\Http\Controllers\Link\CreateLinkController;
use App\Http\Controllers\Link\UpdateLinkController;
use App\Http\Controllers\Redirect\RedirectLinkController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canRegister' => Features::enabled(Features::registration()),
    ]);
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('links', \App\Http\Controllers\Link\IndexLinkController::class)
        ->name('links.index');

    Route::post('links', CreateLinkController::class)
        ->name('links.create');

    Route::put('links/{link}', UpdateLinkController::class)
        ->name('links.update');

    Route::get('redirect/{link}', RedirectLinkController::class)
        ->name('links.redirect');

    Route::get('tags', \App\Http\Controllers\Tag\SearchTagsController::class)
        ->name('tags.index');
});

require __DIR__.'/settings.php';
