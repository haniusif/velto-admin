<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

/**
 * Run an artisan command inside this process rather than spawning one.
 *
 * Schedule::command() shells out through Symfony Process, which needs
 * proc_open — disabled on the production host. The scheduler still printed
 * "DONE" for every task while the subprocess threw, so all four jobs silently
 * did nothing: bookings were never dispatched to a worker, stale bookings and
 * plans were never released, and lapsed packages never expired.
 *
 * Artisan::call() runs in-process, so nothing is spawned and the work actually
 * happens. Exceptions are caught and logged per task, because one failing job
 * must not stop the others in the same tick.
 */
// A closure rather than a named function: this file is loaded more than once
// in some contexts (the test suite among them), and a global function would
// fatal on redeclaration.
$scheduled = static fn (string $command): callable => static function () use ($command): void {
    try {
        Artisan::call($command);
    } catch (\Throwable $e) {
        \Illuminate\Support\Facades\Log::error('Scheduled command failed', [
            'command' => $command,
            'message' => $e->getMessage(),
        ]);
    }
};

// Release unpaid pending bookings so they never linger as "booked".
// Grace window is configurable in admin settings (booking.pending_grace_minutes).
Schedule::call($scheduled('bookings:cancel-stale'))->name('bookings-cancel-stale')->everyFiveMinutes();

// Dispatch backstop: expire stale offers, retry the waiting-assignment queue.
// A closure has no name to derive a mutex from, so withoutOverlapping()
// needs one given explicitly — otherwise it throws at schedule time.
Schedule::call($scheduled('dispatch:sweep'))
    ->name('dispatch-sweep')
    ->everyMinute()
    ->withoutOverlapping();

// A plan whose card payment was abandoned leaves an unusable "awaiting
// payment" card in the customer's account otherwise. Same cadence as the
// booking equivalent it mirrors.
Schedule::call($scheduled('packages:cancel-stale'))->name('packages-cancel-stale')->everyFiveMinutes();

// Settle lapsed prepaid plans. Expiry is already enforced live, so this only
// keeps the stored status honest for reporting — hourly is ample.
Schedule::call($scheduled('packages:expire'))->name('packages-expire')->hourly();
