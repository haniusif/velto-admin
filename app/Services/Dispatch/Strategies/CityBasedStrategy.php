<?php

namespace App\Services\Dispatch\Strategies;

use App\Models\Appointment;
use App\Models\Worker;
use Illuminate\Support\Collection;

/**
 * Filter to workers in the job's city, then pick the least-loaded among them.
 * Shippable on today's data — `worker.city` is the only geographic attribute
 * that already exists. Falls back to the full pool when the job has no city.
 */
class CityBasedStrategy implements AssignmentStrategy
{
    public function pick(Appointment $appointment, Collection $eligible): ?Worker
    {
        $city = $this->jobCity($appointment);

        $pool = $city === null
            ? $eligible
            : $eligible->filter(fn (Worker $w) => $this->norm($w->city) === $this->norm($city));

        if ($pool->isEmpty()) {
            $pool = $eligible; // no local worker → don't strand the job
        }

        return $pool->sortBy(fn (Worker $w) => $w->openJobsCount())->first();
    }

    private function jobCity(Appointment $appointment): ?string
    {
        $area = $appointment->area;

        return $area?->city?->name ?? null;
    }

    private function norm(?string $s): string
    {
        return mb_strtolower(trim((string) $s));
    }

    public function key(): string
    {
        return self::CITY_BASED;
    }
}
