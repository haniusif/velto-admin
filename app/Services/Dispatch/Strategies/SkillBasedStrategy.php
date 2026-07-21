<?php

namespace App\Services\Dispatch\Strategies;

use App\Models\Appointment;
use App\Models\Worker;
use App\Services\Dispatch\WorkerScoringService;
use Illuminate\Support\Collection;

/**
 * Ensure the worker has the service's required skill (already a hard eligibility
 * gate, re-checked here defensively), then pick the highest-scored candidate.
 */
class SkillBasedStrategy implements AssignmentStrategy
{
    public function __construct(private readonly WorkerScoringService $scoring) {}

    public function pick(Appointment $appointment, Collection $eligible): ?Worker
    {
        $skillId = $appointment->washPackage?->required_skill_id;

        $pool = $skillId === null
            ? $eligible
            : $eligible->filter(fn (Worker $w) => $w->hasSkill($skillId));

        if ($pool->isEmpty()) {
            return null; // required skill unmet — leave it to the queue
        }

        return $pool
            ->sortByDesc(fn (Worker $w) => $this->scoring->score($w, $appointment))
            ->first();
    }

    public function key(): string
    {
        return self::SKILL_BASED;
    }
}
