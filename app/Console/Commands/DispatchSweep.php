<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Models\AssignmentOffer;
use App\Services\Dispatch\DispatchService;
use App\Support\DispatchState;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Backstop for the dispatch engine. Runs every minute to:
 *   1. expire offers whose countdown elapsed (in case a delayed job was missed),
 *   2. re-dispatch jobs parked in the waiting queue.
 * The delayed ExpireOffer job is the primary path; this is the safety net.
 */
class DispatchSweep extends Command
{
    protected $signature = 'dispatch:sweep';

    protected $description = 'Expire stale offers and retry waiting-assignment jobs';

    public function handle(DispatchService $dispatch): int
    {
        Cache::put('velto.dispatch.last_sweep', now()->timestamp);

        // 1) Expire overdue offers.
        $overdue = AssignmentOffer::query()
            ->where('status', AssignmentOffer::STATUS_OFFERED)
            ->where('expires_at', '<', now())
            ->get();

        foreach ($overdue as $offer) {
            $dispatch->expireOffer($offer);
        }

        // 2) Retry jobs waiting for a worker (still active, in the future).
        $waiting = Appointment::query()
            ->where('dispatch_state', DispatchState::WAITING)
            ->whereNull('worker_id')
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->limit(200)
            ->get();

        foreach ($waiting as $appointment) {
            $dispatch->dispatch($appointment);
        }

        $this->info("Sweep: expired {$overdue->count()} offer(s), retried {$waiting->count()} waiting job(s).");

        return self::SUCCESS;
    }
}
