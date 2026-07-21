<?php

namespace App\Services\Dispatch;

use App\Models\Appointment;
use App\Models\AssignmentOffer;
use App\Models\Worker;
use App\Support\DispatchState;
use Illuminate\Support\Collection;

/**
 * Read model for the Dispatch Center & Board — KPIs, worker roster, the waiting
 * queue, and the pipeline grouped by dispatch state. All derived from the
 * appointments + assignment_offers tables.
 */
class DispatchStats
{
    /** @return array<string,int|float> */
    public function kpis(): array
    {
        $onlineWorkers = Worker::where('status', 'active')->where('is_online', true)->count();
        $activeWorkers = Worker::where('status', 'active')->count();

        $waiting = Appointment::where('dispatch_state', DispatchState::WAITING)->count();
        $offered = Appointment::where('dispatch_state', DispatchState::OFFERED)->count();
        $scheduled = Appointment::where('dispatch_state', DispatchState::SCHEDULED)->count();

        $todayJobs = Appointment::whereDate('scheduled_at', today())
            ->whereNotIn('status', [Appointment::STATUS_CANCELLED])->count();

        // At-risk: scheduled time passed but the worker isn't on the way / started.
        $atRisk = Appointment::whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<', now())
            ->count();

        // Auto-assign success: engine-created offers that landed a worker.
        $autoTotal = AssignmentOffer::where('reason', 'auto')->where('created_at', '>=', now()->subDays(7))->count();
        $autoWon = AssignmentOffer::where('reason', 'auto')->where('created_at', '>=', now()->subDays(7))
            ->whereIn('status', [AssignmentOffer::STATUS_ASSIGNED, AssignmentOffer::STATUS_ACCEPTED])->count();

        return [
            'online_workers' => $onlineWorkers,
            'active_workers' => $activeWorkers,
            'waiting' => $waiting,
            'offered' => $offered,
            'scheduled' => $scheduled,
            'today_jobs' => $todayJobs,
            'at_risk' => $atRisk,
            'auto_success' => $autoTotal > 0 ? (int) round(100 * $autoWon / $autoTotal) : 0,
        ];
    }

    /** @return Collection<int,array<string,mixed>> */
    public function roster(): Collection
    {
        return Worker::query()
            ->where('status', 'active')
            ->orderByDesc('is_online')
            ->orderBy('name')
            ->get()
            ->map(function (Worker $w): array {
                $open = $w->openJobsCount();
                $cap = max(1, (int) $w->max_concurrent_jobs);
                $current = $w->appointments()
                    ->whereIn('status', Appointment::ACTIVE_STATUSES)
                    ->orderBy('scheduled_at')
                    ->first();

                return [
                    'id' => $w->id,
                    'name' => $w->name,
                    'online' => (bool) $w->is_online,
                    'last_seen' => $w->last_seen_at,
                    'open' => $open,
                    'today' => $w->jobsTodayCount(),
                    'cap' => $cap,
                    'daily_cap' => max(1, (int) $w->max_jobs_per_day),
                    'utilization' => (int) round(100 * min(1, $open / $cap)),
                    'rating' => $w->rating,
                    'current' => $current?->service_name,
                    'current_at' => $current?->scheduled_at,
                ];
            });
    }

    /** @return Collection<int,Appointment> */
    public function waitingQueue(): Collection
    {
        return Appointment::query()
            ->where('dispatch_state', DispatchState::WAITING)
            ->whereNull('worker_id')
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->orderBy('scheduled_at')
            ->limit(50)
            ->get();
    }

    /**
     * Pipeline grouped by dispatch state for the Kanban board.
     *
     * @return array<string,Collection<int,Appointment>>
     */
    public function board(int $perColumn = 20): array
    {
        $columns = [
            DispatchState::WAITING,
            DispatchState::SCHEDULED,
            DispatchState::AUTO_ASSIGNING,
            DispatchState::OFFERED,
            DispatchState::ASSIGNED,
            DispatchState::EN_ROUTE,
            DispatchState::WORKING,
        ];

        // Map current job statuses onto the two "live" board columns so jobs that
        // moved via the worker app still show up.
        $out = [];
        foreach ($columns as $state) {
            $out[$state] = collect();
        }

        $rows = Appointment::query()
            ->with('worker')
            ->whereIn('status', Appointment::ACTIVE_STATUSES)
            ->whereDate('scheduled_at', '>=', today())
            ->orderBy('scheduled_at')
            ->limit(300)
            ->get();

        foreach ($rows as $a) {
            $col = match ($a->status) {
                Appointment::STATUS_ON_THE_WAY, Appointment::STATUS_ARRIVED => DispatchState::EN_ROUTE,
                Appointment::STATUS_IN_PROGRESS => DispatchState::WORKING,
                default => $a->dispatch_state ?: DispatchState::AUTO_ASSIGNING,
            };
            if (! isset($out[$col])) {
                $out[$col] = collect();
            }
            if ($out[$col]->count() < $perColumn) {
                $out[$col]->push($a);
            }
        }

        return $out;
    }
}
