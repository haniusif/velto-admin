<?php

namespace App\Services\Dispatch\Strategies;

use App\Models\Appointment;
use App\Models\Worker;
use App\Services\Dispatch\WorkerScoringService;
use Illuminate\Support\Collection;

/**
 * The default intelligent strategy: score every eligible worker with the
 * weighted engine and pick the highest. Ties break to the higher rating.
 */
class AutoStrategy implements AssignmentStrategy
{
    public function __construct(private readonly WorkerScoringService $scoring) {}

    public function pick(Appointment $appointment, Collection $eligible): ?Worker
    {
        if ($eligible->isEmpty()) {
            return null;
        }

        return $eligible
            ->map(fn (Worker $w) => [
                'worker' => $w,
                // score in [0,1] dominates; rating in [0,5] breaks ties.
                'rank' => $this->scoring->score($w, $appointment) * 1000 + (float) ($w->rating ?? 0),
            ])
            ->sortByDesc('rank')
            ->first()['worker'];
    }

    public function key(): string
    {
        return self::AUTO;
    }
}
