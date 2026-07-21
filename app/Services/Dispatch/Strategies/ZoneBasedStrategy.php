<?php

namespace App\Services\Dispatch\Strategies;

use App\Models\Appointment;
use App\Models\Worker;
use App\Services\Dispatch\WorkerScoringService;
use Illuminate\Support\Collection;

/**
 * Filter to workers who cover the job's zone, then pick the highest-scored.
 * Falls back to the full pool when no worker declares that zone, so a job is
 * never stranded on incomplete zone coverage.
 */
class ZoneBasedStrategy implements AssignmentStrategy
{
    public function __construct(private readonly WorkerScoringService $scoring) {}

    public function pick(Appointment $appointment, Collection $eligible): ?Worker
    {
        $zoneId = $appointment->zone_id;

        $pool = $zoneId === null
            ? $eligible
            : $eligible->filter(fn (Worker $w) => $w->coversZone($zoneId));

        if ($pool->isEmpty()) {
            $pool = $eligible;
        }

        return $pool
            ->sortByDesc(fn (Worker $w) => $this->scoring->score($w, $appointment))
            ->first();
    }

    public function key(): string
    {
        return self::ZONE_BASED;
    }
}
