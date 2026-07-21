<?php

namespace App\Services\Dispatch\Strategies;

use App\Models\Appointment;
use App\Models\Worker;
use Illuminate\Support\Collection;

interface AssignmentStrategy
{
    public const MANUAL = 'manual';
    public const AUTO = 'auto';
    public const ROUND_ROBIN = 'round_robin';
    public const LEAST_LOADED = 'least_loaded';
    public const NEAREST = 'nearest';
    public const SKILL_BASED = 'skill_based';
    public const ZONE_BASED = 'zone_based';
    public const CITY_BASED = 'city_based';
    public const PREFERRED = 'preferred';
    public const EMERGENCY = 'emergency';

    /**
     * Pick a worker from the already-eligible pool, or null if none fit this
     * strategy's rule (the engine then queues the job as waiting).
     *
     * @param  Collection<int,Worker>  $eligible
     */
    public function pick(Appointment $appointment, Collection $eligible): ?Worker;

    public function key(): string;
}
