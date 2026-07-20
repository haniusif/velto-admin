<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\AppSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * A card booking that isn't paid promptly isn't a booking. This cancels
 * pending, unpaid bookings older than the grace window. They hold no seat
 * (seats are only consumed on payment capture), so no slot release is needed.
 */
class CancelStalePendingBookings extends Command
{
    protected $signature = 'bookings:cancel-stale {--minutes=}';

    protected $description = 'Cancel unpaid pending bookings older than the configured grace window';

    public function handle(): int
    {
        // Heartbeat: proves the scheduler (cron) is actually running.
        Cache::put('velto.scheduler.last_run', now()->timestamp);

        // --minutes overrides; otherwise use the admin setting (default 30).
        $minutes = $this->option('minutes')
            ?? AppSetting::get('booking.pending_grace_minutes', '30');
        $minutes = max(1, (int) $minutes);

        $cutoff = now()->subMinutes($minutes);

        $count = Appointment::query()
            ->where('status', Appointment::STATUS_PENDING)
            ->where('payment_status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->update([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

        $this->info("Cancelled {$count} stale pending booking(s) older than {$minutes} min.");

        return self::SUCCESS;
    }
}
