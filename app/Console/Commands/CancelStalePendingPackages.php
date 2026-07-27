<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\AppSetting;
use App\Models\CustomerPackage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * A plan whose card payment was never completed isn't a plan. Bookings already
 * get this treatment (bookings:cancel-stale); without the equivalent, an
 * abandoned purchase leaves an "awaiting payment" card in the customer's
 * account forever, with visits they can never spend.
 *
 * Nothing was consumed by such a purchase — visits and seats are only taken on
 * capture — so cancelling is pure cleanup. Any first visit booked alongside it
 * goes too: there is no plan left to draw a visit from.
 */
class CancelStalePendingPackages extends Command
{
    protected $signature = 'packages:cancel-stale {--minutes=}';

    protected $description = 'Cancel unpaid pending plans older than the configured grace window';

    public function handle(): int
    {
        // Shares the booking grace window: both are "customer walked away from
        // the hosted page", and two knobs for one behaviour would drift.
        $minutes = $this->option('minutes')
            ?? AppSetting::get('booking.pending_grace_minutes', '30');
        $minutes = max(1, (int) $minutes);

        $cutoff = now()->subMinutes($minutes);

        $plans = CustomerPackage::query()
            ->where('status', CustomerPackage::STATUS_PENDING)
            ->where('payment_status', 'pending')
            ->where('created_at', '<', $cutoff)
            ->get();

        $count = 0;

        foreach ($plans as $plan) {
            DB::transaction(function () use ($plan, &$count) {
                $plan->update([
                    'status' => CustomerPackage::STATUS_CANCELLED,
                    'payment_status' => 'failed',
                ]);

                // The first visit, if one was booked with it. Still pending and
                // still holding nothing, so there is no seat to release.
                $plan->appointments()
                    ->where('status', Appointment::STATUS_PENDING)
                    ->update([
                        'status' => Appointment::STATUS_CANCELLED,
                        'cancelled_at' => now(),
                    ]);

                $count++;
            });
        }

        $this->info("Cancelled {$count} stale pending plan(s) older than {$minutes} min.");

        return self::SUCCESS;
    }
}
