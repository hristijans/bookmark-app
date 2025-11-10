<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Nightly sync of the links search index and ensure index settings are up to date
Schedule::command('links:scout-sync')->dailyAt('02:20');

// Fetch user feeds every 12 hours
Schedule::command('feeds:sync')->twiceDaily(1, 13)->withoutOverlapping();
