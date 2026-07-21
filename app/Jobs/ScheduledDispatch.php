<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\Dispatch\DispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Delayed until (scheduled_at − dispatch_lead) for a future booking. Fires the
 * engine when the appointment is close enough to assign against current worker
 * load/location. The dispatch:sweep cron is the backstop if this is missed.
 */
class ScheduledDispatch implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $appointmentId) {}

    public function handle(DispatchService $dispatch): void
    {
        $appointment = Appointment::find($this->appointmentId);
        if ($appointment !== null) {
            $dispatch->dispatch($appointment);
        }
    }
}
