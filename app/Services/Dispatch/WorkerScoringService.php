<?php

namespace App\Services\Dispatch;

use App\Models\Appointment;
use App\Models\Worker;
use App\Support\Geo;

/**
 * Weighted worker scoring. Each factor is normalized to [0,1] then combined by
 * the configurable weights in DispatchSettings. Missing data degrades to a
 * neutral value so a worker is never unfairly penalized (e.g. no location yet).
 */
class WorkerScoringService
{
    /** @var array<int,?int> customer_id => preferred (last) worker id */
    private array $preferredCache = [];

    public function __construct(private readonly DispatchSettings $settings) {}

    public function score(Worker $worker, Appointment $appointment): float
    {
        return $this->evaluate($worker, $appointment)['score'];
    }

    /** @return array{score:float,factors:array<string,float>} */
    public function evaluate(Worker $worker, Appointment $appointment): array
    {
        $weights = $this->settings->weights();
        $factors = [
            'distance' => $this->distanceFactor($worker, $appointment),
            'workload' => $this->workloadFactor($worker),
            'jobs_today' => $this->jobsTodayFactor($worker),
            'rating' => $this->ratingFactor($worker),
            'acceptance' => $this->acceptanceFactor($worker),
            'same_zone' => $this->sameZoneFactor($worker, $appointment),
            'preferred' => $this->preferredFactor($worker, $appointment),
        ];

        $sum = 0.0;
        $wSum = 0.0;
        foreach ($factors as $key => $value) {
            $w = (float) ($weights[$key] ?? 0);
            $sum += $w * $value;
            $wSum += $w;
        }

        return [
            'score' => $wSum > 0 ? round($sum / $wSum, 4) : 0.0,
            'factors' => array_map(fn ($v) => round($v, 3), $factors),
        ];
    }

    private function distanceFactor(Worker $worker, Appointment $appointment): float
    {
        if ($worker->last_lat === null || $worker->last_lng === null
            || $appointment->latitude === null || $appointment->longitude === null) {
            return 0.5; // neutral when location is unknown
        }

        $radius = max(1, $this->settings->int('distance_radius_km'));
        $km = Geo::haversineKm(
            (float) $worker->last_lat, (float) $worker->last_lng,
            (float) $appointment->latitude, (float) $appointment->longitude,
        );

        return 1 - min(1.0, $km / $radius); // closer = higher
    }

    private function workloadFactor(Worker $worker): float
    {
        $cap = max(1, (int) $worker->max_concurrent_jobs);

        return 1 - min(1.0, $worker->openJobsCount() / $cap);
    }

    private function jobsTodayFactor(Worker $worker): float
    {
        $cap = max(1, (int) $worker->max_jobs_per_day);

        return 1 - min(1.0, $worker->jobsTodayCount() / $cap);
    }

    private function ratingFactor(Worker $worker): float
    {
        return min(1.0, ((float) ($worker->rating ?? 3.5)) / 5);
    }

    private function acceptanceFactor(Worker $worker): float
    {
        return $worker->acceptance_rate === null
            ? 1.0 // benefit of the doubt for new workers
            : max(0.0, min(1.0, (float) $worker->acceptance_rate));
    }

    private function sameZoneFactor(Worker $worker, Appointment $appointment): float
    {
        $city = $appointment->area?->city?->name;
        if ($city === null || $worker->city === null) {
            return 0.0;
        }

        return mb_strtolower(trim($worker->city)) === mb_strtolower(trim($city)) ? 1.0 : 0.0;
    }

    private function preferredFactor(Worker $worker, Appointment $appointment): float
    {
        return $worker->id === $this->preferredWorkerId($appointment) ? 1.0 : 0.0;
    }

    private function preferredWorkerId(Appointment $appointment): ?int
    {
        $customerId = $appointment->customer_id;
        if ($customerId === null) {
            return null;
        }

        return $this->preferredCache[$customerId] ??= Appointment::query()
            ->where('customer_id', $customerId)
            ->whereNotNull('worker_id')
            ->where('id', '!=', $appointment->id)
            ->latest('id')
            ->value('worker_id');
    }
}
