<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\Dispatch\DispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Runs the dispatch engine for one job off the request path. Idempotent —
 * DispatchService::dispatch re-checks that the job still needs a worker.
 */
class RunDispatch implements ShouldQueue
{
    use Queueable;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(public readonly int $appointmentId) {}

    public function handle(DispatchService $dispatch): void
    {
        $appointment = Appointment::find($this->appointmentId);
        if ($appointment !== null) {
            $dispatch->dispatch($appointment);
        }
    }
}
