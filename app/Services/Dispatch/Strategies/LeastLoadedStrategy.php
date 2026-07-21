<?php

namespace App\Services\Dispatch\Strategies;

use App\Models\Appointment;
use App\Models\Worker;
use Illuminate\Support\Collection;

/**
 * Fewest open jobs wins; ties break to the higher rating, then the worker
 * idle longest (spreads load). The default recommendation for Phase 1.
 */
class LeastLoadedStrategy implements AssignmentStrategy
{
    public function pick(Appointment $appointment, Collection $eligible): ?Worker
    {
        return $eligible
            ->sortBy([
                fn (Worker $w) => $w->openJobsCount(),
                fn (Worker $w) => -1 * (float) ($w->rating ?? 0),
            ])
            ->first();
    }

    public function key(): string
    {
        return self::LEAST_LOADED;
    }
}
