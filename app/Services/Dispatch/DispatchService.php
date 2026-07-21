<?php

namespace App\Services\Dispatch;

use App\Jobs\ExpireOffer;
use App\Jobs\ScheduledDispatch;
use App\Models\Appointment;
use App\Models\AssignmentOffer;
use App\Models\Worker;
use App\Services\Dispatch\Strategies\AssignmentStrategy;
use App\Services\Dispatch\Strategies\StrategyFactory;
use App\Services\Notifications\NotificationDispatcher;
use App\Support\DispatchState;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/**
 * The single seam every assignment path flows through — booking, admin action,
 * cron/queue tick, and reassignment. Resolves a strategy, filters to eligible
 * workers, and either assigns directly or opens an offer, queueing the job as
 * "waiting" when nobody fits. Idempotent: safe to re-run for the same job.
 */
class DispatchService
{
    public function __construct(
        private readonly DispatchSettings $settings,
        private readonly WorkerEligibilityService $eligibility,
        private readonly StrategyFactory $strategies,
        private readonly NotificationDispatcher $notifications,
        private readonly WorkerScoringService $scoring,
    ) {}

    /** Entry point: try to (re)assign a worker to a job that needs one. */
    public function dispatch(Appointment $appointment): void
    {
        $appointment->refresh();

        if (! $this->settings->autoDispatchEnabled() || ! $appointment->needsDispatch()) {
            return;
        }

        $strategy = $this->resolveStrategy($appointment);
        if ($strategy->key() === AssignmentStrategy::MANUAL) {
            return; // leave for a human
        }

        // Scheduled booking: defer dispatch to lead-time before the appointment,
        // so the engine assigns against current load/location, not hours ahead.
        if ($this->shouldDefer($appointment)) {
            $this->scheduleDeferred($appointment);

            return;
        }

        // Bound total dispatch runs; exhaustion parks the job for an operator.
        if ($appointment->dispatch_attempts >= $this->settings->int('max_retries')) {
            $this->queueWaiting($appointment, escalate: true);

            return;
        }

        $appointment->increment('dispatch_attempts');
        $appointment->update(['dispatch_state' => DispatchState::AUTO_ASSIGNING]);

        $eligible = $this->eligibility->eligibleFor($appointment);
        $worker = $eligible->isNotEmpty() ? $strategy->pick($appointment, $eligible) : null;

        if ($worker === null) {
            $this->queueWaiting($appointment);

            return;
        }

        ['score' => $score, 'factors' => $factors] = $this->scoring->evaluate($worker, $appointment);

        $this->settings->usesOffers()
            ? $this->offer($appointment, $worker, 'auto', $score, $factors)
            : $this->assign($appointment, $worker, 'auto', $score, $factors);
    }

    private function shouldDefer(Appointment $appointment): bool
    {
        if ($appointment->scheduled_at === null) {
            return false;
        }
        $minutesUntil = ($appointment->scheduled_at->getTimestamp() - now()->getTimestamp()) / 60;

        return $minutesUntil > $this->settings->int('immediate_threshold_minutes');
    }

    private function scheduleDeferred(Appointment $appointment): void
    {
        if ($appointment->dispatch_state === DispatchState::SCHEDULED) {
            return; // already queued for its lead time
        }

        $appointment->update(['dispatch_state' => DispatchState::SCHEDULED]);

        $fireAt = $appointment->scheduled_at->copy()
            ->subMinutes($this->settings->int('dispatch_lead_minutes'));

        ScheduledDispatch::dispatch($appointment->id)->delay($fireAt)->afterCommit();
    }

    /**
     * On-demand admin auto-assign: pick the best eligible worker and commit them
     * directly (ignores offer mode — the operator wants it done). Falls back to
     * least-loaded when the active strategy is manual. Returns the assigned
     * worker, or null when no eligible worker exists.
     */
    public function autoAssign(Appointment $appointment): ?Worker
    {
        $appointment->refresh();
        if ($appointment->worker_id !== null) {
            return $appointment->worker;
        }

        $strategy = $this->resolveStrategy($appointment);
        if ($strategy->key() === AssignmentStrategy::MANUAL) {
            $strategy = $this->strategies->make(AssignmentStrategy::LEAST_LOADED);
        }

        $eligible = $this->eligibility->eligibleFor($appointment);
        $worker = $eligible->isNotEmpty() ? $strategy->pick($appointment, $eligible) : null;

        if ($worker !== null) {
            ['score' => $score, 'factors' => $factors] = $this->scoring->evaluate($worker, $appointment);
            $this->assign($appointment, $worker, 'admin_auto', $score, $factors);
        } else {
            $this->queueWaiting($appointment);
        }

        return $worker;
    }

    /** Commit a worker to a job immediately (direct mode, admin force-assign). */
    public function assign(
        Appointment $appointment, Worker $worker, string $reason = 'manual',
        ?float $score = null, ?array $factors = null
    ): AssignmentOffer {
        return DB::transaction(function () use ($appointment, $worker, $reason, $score, $factors) {
            $previous = $appointment->worker_id;

            $offer = $appointment->offers()->create([
                'worker_id' => $worker->id,
                'status' => AssignmentOffer::STATUS_ASSIGNED,
                'reason' => $reason,
                'score' => $score,
                'factors' => $factors,
                'attempt' => $appointment->dispatch_attempts,
                'offered_at' => now(),
                'responded_at' => now(),
            ]);

            $appointment->update([
                'worker_id' => $worker->id,
                'dispatch_state' => DispatchState::ASSIGNED,
                'last_offer_id' => $offer->id,
            ]);
            // The worker_id change fires WorkerAssigned → worker "assigned" notification.

            $this->notifications->customerWorkerAssigned($appointment->fresh(['worker']), changed: $previous !== null);
            if ($previous !== null && $previous !== $worker->id) {
                $this->notifications->workerReassignedAway($previous, $appointment);
            }

            return $offer;
        });
    }

    /** Open a consented offer with a countdown (offer mode). */
    public function offer(
        Appointment $appointment, Worker $worker, string $reason = 'auto',
        ?float $score = null, ?array $factors = null
    ): AssignmentOffer {
        $timeout = $this->settings->int('acceptance_timeout_seconds');

        return DB::transaction(function () use ($appointment, $worker, $reason, $timeout, $score, $factors) {
            $offer = $appointment->offers()->create([
                'worker_id' => $worker->id,
                'status' => AssignmentOffer::STATUS_OFFERED,
                'reason' => $reason,
                'score' => $score,
                'factors' => $factors,
                'attempt' => $appointment->dispatch_attempts,
                'offered_at' => now(),
                'expires_at' => now()->addSeconds($timeout),
            ]);

            $appointment->update([
                'dispatch_state' => DispatchState::OFFERED,
                'last_offer_id' => $offer->id,
            ]);

            ExpireOffer::dispatch($offer->id)->delay(now()->addSeconds($timeout))->afterCommit();
            $this->notifications->workerOffered($offer);

            return $offer;
        });
    }

    /** Worker accepted an offer → commit them and stamp the job accepted. */
    public function acceptOffer(AssignmentOffer $offer): Appointment
    {
        return DB::transaction(function () use ($offer) {
            $offer = AssignmentOffer::whereKey($offer->id)->lockForUpdate()->first();

            if ($offer === null || ! $offer->isPending()) {
                throw ValidationException::withMessages(['offer' => ['This offer is no longer available.']]);
            }
            if ($offer->isExpired()) {
                $this->expireOffer($offer);
                throw ValidationException::withMessages(['offer' => ['This offer has expired.']]);
            }

            $worker = $offer->worker;
            $appointment = $offer->appointment;

            if ($appointment->worker_id !== null) {
                throw ValidationException::withMessages(['offer' => ['This job was already taken.']]);
            }
            if (! $worker->isUnderConcurrentCap()) {
                $offer->update(['status' => AssignmentOffer::STATUS_REJECTED, 'responded_at' => now(), 'reason' => 'over_capacity']);
                throw ValidationException::withMessages(['offer' => ['You are at your job limit.']]);
            }

            $offer->update(['status' => AssignmentOffer::STATUS_ACCEPTED, 'responded_at' => now()]);
            $appointment->update([
                'worker_id' => $worker->id,
                'dispatch_state' => DispatchState::ASSIGNED,
                'accepted_at' => now(),
            ]);
            $this->notifications->customerWorkerAssigned($appointment->fresh(['worker']));

            return $appointment;
        });
    }

    /** Worker declined → record it and re-dispatch to the next candidate. */
    public function rejectOffer(AssignmentOffer $offer, string $reason = 'rejected'): void
    {
        if ($offer->isPending()) {
            $offer->update(['status' => AssignmentOffer::STATUS_REJECTED, 'responded_at' => now(), 'reason' => $reason]);
        }
        $this->redispatch($offer->appointment);
    }

    /** Offer countdown elapsed (job/sweep) → expire and re-dispatch. */
    public function expireOffer(AssignmentOffer $offer): void
    {
        if (! $offer->isPending()) {
            return;
        }
        $offer->update(['status' => AssignmentOffer::STATUS_EXPIRED, 'responded_at' => now(), 'reason' => 'timeout']);
        $this->redispatch($offer->appointment);
    }

    /** Force reassignment (admin, worker offline/no-show) excluding the current worker. */
    public function reassign(Appointment $appointment, string $reason = 'reassigned'): void
    {
        $previous = $appointment->worker_id;

        if ($previous !== null) {
            // Record as a decline so eligibility excludes them from the retry.
            $appointment->offers()->create([
                'worker_id' => $previous,
                'status' => AssignmentOffer::STATUS_REJECTED,
                'reason' => $reason,
                'attempt' => $appointment->dispatch_attempts,
                'offered_at' => now(),
                'responded_at' => now(),
            ]);
        }

        $appointment->update([
            'worker_id' => null,
            'accepted_at' => null,
            'started_at' => null,
            'dispatch_state' => DispatchState::AUTO_ASSIGNING,
        ]);

        if ($previous !== null) {
            $this->notifications->workerReassignedAway($previous, $appointment);
        }

        $this->dispatch($appointment->fresh());
    }

    // --- internals -------------------------------------------------------

    private function redispatch(Appointment $appointment): void
    {
        $appointment->refresh();
        if ($appointment->worker_id !== null || $appointment->assignment_locked || ! $appointment->auto_dispatch) {
            return;
        }
        $this->dispatch($appointment);
    }

    private function resolveStrategy(Appointment $appointment): AssignmentStrategy
    {
        $key = $appointment->dispatch_strategy ?: $this->settings->string('default_strategy');

        return $this->strategies->make($key);
    }

    private function queueWaiting(Appointment $appointment, bool $escalate = false): void
    {
        $appointment->update(['dispatch_state' => DispatchState::WAITING]);

        if ($escalate) {
            Log::warning('[dispatch] no worker after max retries — escalating', [
                'appointment_id' => $appointment->id,
                'attempts' => $appointment->dispatch_attempts,
            ]);
            // Admin escalation notification lands with the Dispatch Center (Phase 3).
        }
    }
}
