<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/*
|--------------------------------------------------------------------------
| Scheduled Tasks
|--------------------------------------------------------------------------
*/

// Regenerate sitemap daily at 2 AM Nairobi time + ping search engines
Schedule::command('sitemap:regenerate --ping')->dailyAt('02:00')->timezone('Africa/Nairobi');

// Expire unpaid orders older than 30 minutes (free up inventory)
Schedule::command('orders:expire-pending')->everyFiveMinutes();

// Clear stale homepage cache to pick up newly published events
Schedule::command('cache:forget home:featured_events')->hourly();
Schedule::command('cache:forget home:upcoming_events')->hourly();
Schedule::command('cache:forget home:weekend_events')->everyThirtyMinutes();
