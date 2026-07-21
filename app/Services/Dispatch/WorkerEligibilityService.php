<?php

namespace App\Services\Dispatch;

use App\Models\Appointment;
use App\Models\Worker;
use Illuminate\Support\Collection;

/**
 * Hard filters — who can never be assigned. Runs before any scoring/strategy.
 * Phase 1 gates: active, (optionally) online, under concurrent cap, under daily
 * cap, not previously declined this job, and not conflicting with an existing
 * job at the same time. Geo/skill/zone gates arrive in later phases.
 */
class WorkerEligibilityService
{
    public function __construct(private readonly DispatchSettings $settings) {}

    /** @return Collection<int,Worker> */
    public function eligibleFor(Appointment $appointment): Collection
    {
        $declined = $appointment->declinedWorkerIds();

        $query = Worker::query()->where('status', 'active');

        if ($this->settings->bool('require_online')) {
            $query->where('is_online', true);
        }
        if (! empty($declined)) {
            $query->whereNotIn('id', $declined);
        }

        return $query->get()->filter(
            fn (Worker $w): bool => $this->passes($w, $appointment)
        )->values();
    }

    public function passes(Worker $worker, Appointment $appointment): bool
    {
        if (! $worker->isActive()) {
            return false;
        }
        if ($this->settings->bool('require_online') && ! $worker->is_online) {
            return false;
        }
        if (! $worker->isUnderConcurrentCap()) {
            return false;
        }
        if (! $worker->isUnderDailyCap()) {
            return false;
        }

        return ! $this->hasConflict($worker, $appointment);
    }

    /**
     * Overlap check against the worker's other jobs on the same day. Uses the
     * package duration (WashPackage.duration_minutes) plus a service buffer;
     * travel/traffic buffers are layered in once worker location exists.
     */
    public function hasConflict(Worker $worker, Appointment $appointment): bool
    {
        if ($appointment->scheduled_at === null) {
            return false;
        }

        $buffer = 10; // service buffer, minutes (config in later phase)
        $duration = (int) ($appointment->washPackage?->duration_minutes ?: 60);
        $start = $appointment->scheduled_at->copy();
        $end = $start->copy()->addMinutes($duration + $buffer);

        return $worker->appointments()
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->when($appointment->exists, fn ($q) => $q->where('id', '!=', $appointment->id))
            ->whereDate('scheduled_at', $start->toDateString())
            ->get()
            ->contains(function (Appointment $other) use ($start, $end, $buffer): bool {
                if ($other->scheduled_at === null) {
                    return false;
                }
                $oStart = $other->scheduled_at->copy();
                $oDur = (int) ($other->washPackage?->duration_minutes ?: 60);
                $oEnd = $oStart->copy()->addMinutes($oDur + $buffer);

                // standard interval overlap
                return $start->lt($oEnd) && $end->gt($oStart);
            });
    }
}
