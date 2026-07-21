<?php

namespace App\Events;

use App\Models\Appointment;
use Illuminate\Foundation\Events\Dispatchable;

/** Fired when a job's worker_id changes to a non-null worker. */
class WorkerAssigned
{
    use Dispatchable;

    public function __construct(public readonly Appointment $appointment) {}
}
