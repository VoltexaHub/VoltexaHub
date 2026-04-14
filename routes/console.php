<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Weekly digest: Mondays at 09:00 in the app timezone.
Schedule::command('app:send-digest')->weeklyOn(1, '09:00')->withoutOverlapping();
