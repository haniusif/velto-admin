<?php

namespace App\Services\Dispatch\Strategies;

use App\Models\Appointment;
use App\Models\Worker;
use Illuminate\Support\Collection;

/**
 * No automatic selection — the job waits for a human to assign it. The engine
 * still validates eligibility and conflicts when an admin does assign.
 */
class ManualStrategy implements AssignmentStrategy
{
    public function pick(Appointment $appointment, Collection $eligible): ?Worker
    {
        return null;
    }

    public function key(): string
    {
        return self::MANUAL;
    }
}
