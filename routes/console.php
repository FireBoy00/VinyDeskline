<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Schedule desk synchronization from API every hour
Schedule::command('desks:sync')->hourly();

// Schedule desk metrics collection every 5 minutes
Schedule::command('desks:collect-metrics')->everyFiveMinutes();

Schedule::command('app:run-schedules')->everyMinute();