<?php

namespace App\Services\Dispatch;

use App\Models\Appointment;
use App\Models\Worker;
use App\Support\Geo;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Advisory route sequencing for a worker's day. Orders their located jobs by
 * nearest-neighbour from the worker's current position to minimise travel and
 * avoid zig-zags. It proposes a sequence; committed time-slots stay the anchors
 * — nothing here silently reschedules a job.
 */
class RouteOptimizer
{
    /** @return Collection<int,Appointment> ordered jobs */
    public function suggestSequence(Worker $worker, ?Carbon $day = null): Collection
    {
        $day ??= today();

        $jobs = $worker->appointments()
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->whereDate('scheduled_at', $day->toDateString())
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->get()
            ->all();

        if (count($jobs) <= 1) {
            return collect($jobs);
        }

        $lat = $worker->last_lat !== null ? (float) $worker->last_lat : (float) $jobs[0]->latitude;
        $lng = $worker->last_lng !== null ? (float) $worker->last_lng : (float) $jobs[0]->longitude;

        $ordered = [];
        while (! empty($jobs)) {
            $bestIndex = 0;
            $bestKm = INF;
            foreach ($jobs as $i => $job) {
                $km = Geo::haversineKm($lat, $lng, (float) $job->latitude, (float) $job->longitude);
                if ($km < $bestKm) {
                    $bestKm = $km;
                    $bestIndex = $i;
                }
            }
            $next = $jobs[$bestIndex];
            $ordered[] = $next;
            $lat = (float) $next->latitude;
            $lng = (float) $next->longitude;
            array_splice($jobs, $bestIndex, 1);
        }

        return collect($ordered);
    }

    /** Total travel distance (km) of a sequence starting from the worker. */
    public function totalDistanceKm(Worker $worker, Collection $sequence): float
    {
        if ($sequence->isEmpty()) {
            return 0.0;
        }

        $lat = $worker->last_lat !== null ? (float) $worker->last_lat : (float) $sequence->first()->latitude;
        $lng = $worker->last_lng !== null ? (float) $worker->last_lng : (float) $sequence->first()->longitude;

        $total = 0.0;
        foreach ($sequence as $job) {
            if ($job->latitude === null || $job->longitude === null) {
                continue;
            }
            $total += Geo::haversineKm($lat, $lng, (float) $job->latitude, (float) $job->longitude);
            $lat = (float) $job->latitude;
            $lng = (float) $job->longitude;
        }

        return round($total, 2);
    }
}
