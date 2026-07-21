<?php

namespace App\Services\Dispatch\Strategies;

use App\Models\Appointment;
use App\Models\Worker;
use App\Services\Dispatch\DispatchSettings;
use App\Support\Geo;
use Illuminate\Support\Collection;

/**
 * Smallest worker→job distance within the configured radius. Workers without a
 * known location, or the whole pool when the job has no coordinates, fall back
 * to least-loaded so a job is never stranded on missing geo data.
 */
class NearestStrategy implements AssignmentStrategy
{
    public function __construct(private readonly DispatchSettings $settings) {}

    public function pick(Appointment $appointment, Collection $eligible): ?Worker
    {
        if ($eligible->isEmpty()) {
            return null;
        }

        if ($appointment->latitude === null || $appointment->longitude === null) {
            return $this->leastLoaded($eligible);
        }

        $radius = max(1, $this->settings->int('distance_radius_km'));

        $located = $eligible
            ->filter(fn (Worker $w) => $w->last_lat !== null && $w->last_lng !== null)
            ->map(fn (Worker $w) => [
                'worker' => $w,
                'km' => Geo::haversineKm(
                    (float) $w->last_lat, (float) $w->last_lng,
                    (float) $appointment->latitude, (float) $appointment->longitude,
                ),
            ])
            ->filter(fn ($e) => $e['km'] <= $radius)
            ->sortBy('km');

        return $located->isNotEmpty()
            ? $located->first()['worker']
            : $this->leastLoaded($eligible);
    }

    private function leastLoaded(Collection $eligible): ?Worker
    {
        return $eligible->sortBy(fn (Worker $w) => $w->openJobsCount())->first();
    }

    public function key(): string
    {
        return self::NEAREST;
    }
}
