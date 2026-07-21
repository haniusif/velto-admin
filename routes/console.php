<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Release unpaid pending bookings so they never linger as "booked".
// Grace window is configurable in admin settings (booking.pending_grace_minutes).
Schedule::command('bookings:cancel-stale')->everyFiveMinutes();

// Dispatch backstop: expire stale offers, retry the waiting-assignment queue.
Schedule::command('dispatch:sweep')->everyMinute()->withoutOverlapping();
