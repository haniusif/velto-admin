<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\AppointmentActivity;
use App\Models\AppointmentReview;
use App\Models\Customer;
use App\Models\DispatchEvent;
use App\Models\PaymentTransaction;
use App\Models\Worker;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

/**
 * Everything that happened to one booking, oldest first: who created and
 * changed it (the change log), each payment attempt, every dispatch decision
 * and the review.
 *
 * Each source already existed — the admin just had to open four screens and
 * line the times up by hand to answer "why was this job late?", and "who
 * cancelled it?" had no answer at all until the change log.
 */
class AppointmentTimeline
{
    /** The *_at stamp each status writes, for orders older than the change log. */
    private const STAMPS = [
        'accepted_at' => null,
        'started_at' => Appointment::STATUS_ON_THE_WAY,
        'arrived_at' => Appointment::STATUS_ARRIVED,
        'work_started_at' => Appointment::STATUS_IN_PROGRESS,
        'completed_at' => Appointment::STATUS_COMPLETED,
        'cancelled_at' => Appointment::STATUS_CANCELLED,
    ];

    private const STATUS_LOOK = [
        Appointment::STATUS_PENDING => ['heroicon-m-clock', 'gray'],
        Appointment::STATUS_CONFIRMED => ['heroicon-m-check', 'info'],
        Appointment::STATUS_ON_THE_WAY => ['heroicon-m-truck', 'warning'],
        Appointment::STATUS_ARRIVED => ['heroicon-m-map-pin', 'warning'],
        Appointment::STATUS_IN_PROGRESS => ['heroicon-m-wrench-screwdriver', 'primary'],
        Appointment::STATUS_COMPLETED => ['heroicon-m-check-badge', 'success'],
        Appointment::STATUS_CANCELLED => ['heroicon-m-x-mark', 'danger'],
    ];

    /**
     * @return Collection<int, array{at: CarbonInterface, title: string, detail: ?string, actor: ?string, changes: list<array{0: string, 1: ?string, 2: ?string}>, icon: string, color: string, planned: bool}>
     */
    public static function for(Appointment $appointment): Collection
    {
        $events = collect();

        $push = function (?CarbonInterface $at, string $title, string $icon, string $color, ?string $detail = null, bool $planned = false, ?string $actor = null, array $changes = []) use ($events): void {
            if ($at !== null) {
                $events->push(compact('at', 'title', 'detail', 'actor', 'changes', 'icon', 'color', 'planned'));
            }
        };

        $activities = AppointmentActivity::where('appointment_id', $appointment->id)->orderBy('id')->get();
        $dispatch = DispatchEvent::where('appointment_id', $appointment->id)->orderBy('id')->get();

        $workerIds = $dispatch->pluck('worker_id')
            ->merge($activities->flatMap(fn ($a) => $a->changes['worker_id'] ?? []))
            ->filter()->unique();
        $workers = Worker::whereIn('id', $workerIds)->pluck('name', 'id');

        // --- Created -------------------------------------------------------
        $created = $activities->firstWhere('event', AppointmentActivity::EVENT_CREATED);
        $push($appointment->created_at ?? $created?->created_at, __('Booked'), 'heroicon-m-plus', 'gray',
            $created ? null : $appointment->customer?->name,
            actor: $created ? self::actor($created) : null);

        // --- Changes -------------------------------------------------------
        // Status changes the log saw, so the bare *_at stamp for the same
        // step is not drawn a second time without its author.
        $loggedStatuses = [];

        foreach (self::mergeBursts($activities) as $activity) {
            if ($activity->event === AppointmentActivity::EVENT_CREATED) {
                continue;
            }

            $changes = $activity->changes ?? [];

            if (isset($changes['status'])) {
                $status = $changes['status'][1];
                $loggedStatuses[] = $status;
                [$icon, $color] = self::STATUS_LOOK[$status] ?? ['heroicon-m-arrow-path', 'gray'];
                $title = self::statusLabel($status);
                unset($changes['status']);
            } elseif (array_keys($changes) === ['worker_id']) {
                [$title, $icon, $color] = $changes['worker_id'][1] === null
                    ? [__('Worker unassigned'), 'heroicon-m-user-minus', 'warning']
                    : [__('Worker assigned'), 'heroicon-m-user-plus', 'primary'];
            } else {
                [$title, $icon, $color] = [__('Order edited'), 'heroicon-m-pencil-square', 'gray'];
            }

            $push($activity->created_at, $title, $icon, $color,
                actor: self::actor($activity),
                changes: self::describe($changes, $workers));
        }

        // --- Status stamps (only where the log has nothing) ----------------
        $worker = $appointment->worker?->name;

        foreach (self::STAMPS as $field => $status) {
            if ($status !== null && in_array($status, $loggedStatuses, true)) {
                continue;
            }
            // The dispatch log's "Worker accepted" is the same moment, with the worker's name.
            if ($field === 'accepted_at' && $dispatch->contains('type', DispatchEvent::TYPE_ACCEPTED)) {
                continue;
            }

            [$icon, $color] = $status ? self::STATUS_LOOK[$status] : ['heroicon-m-check', 'info'];
            $title = $status ? self::statusLabel($status) : __('Accepted');
            $detail = in_array($field, ['accepted_at', 'started_at'], true) ? $worker : null;

            $push($appointment->{$field}, $title, $icon, $color, $detail);
        }

        // --- Payments ------------------------------------------------------
        foreach (PaymentTransaction::where('appointment_id', $appointment->id)->orderBy('id')->get() as $tx) {
            [$title, $color, $icon] = match ($tx->status) {
                PaymentTransaction::STATUS_CAPTURED => [__('Payment captured'), 'success', 'heroicon-m-banknotes'],
                PaymentTransaction::STATUS_FAILED => [__('Payment failed'), 'danger', 'heroicon-m-exclamation-triangle'],
                PaymentTransaction::STATUS_REFUNDED => [__('Payment refunded'), 'warning', 'heroicon-m-arrow-uturn-left'],
                default => [__('Payment started'), 'gray', 'heroicon-m-credit-card'],
            };

            $push($tx->created_at, $title, $icon, $color, collect([
                number_format((float) $tx->amount, 2).' '.($tx->currency ?: 'SAR'),
                $tx->gateway ? strtoupper($tx->gateway) : null,
                $tx->error_text,
            ])->filter()->implode(' · '));
        }

        // --- Dispatch decisions --------------------------------------------
        // An assignment the change log already shows (same worker, within a
        // few seconds) is the same moment seen twice; keep the log's version,
        // which names the person, and skip the engine's.
        $workerMoves = $activities
            ->filter(fn ($a) => isset($a->changes['worker_id']))
            ->map(fn ($a) => [$a->created_at, $a->changes['worker_id'][1]]);

        foreach ($dispatch as $event) {
            $duplicate = in_array($event->type, [DispatchEvent::TYPE_ASSIGNED, DispatchEvent::TYPE_REASSIGNED], true)
                && $workerMoves->contains(fn ($m) => abs($m[0]->diffInSeconds($event->created_at)) <= 5
                    && ($event->type === DispatchEvent::TYPE_REASSIGNED || (string) $m[1] === (string) $event->worker_id));

            if ($duplicate) {
                continue;
            }

            [$title, $color, $icon] = match ($event->type) {
                DispatchEvent::TYPE_SCHEDULED => [__('Dispatch scheduled'), 'gray', 'heroicon-m-clock'],
                DispatchEvent::TYPE_WAITING => [__('Waiting for a worker'), 'warning', 'heroicon-m-pause'],
                DispatchEvent::TYPE_OFFERED => [__('Offered to worker'), 'info', 'heroicon-m-paper-airplane'],
                DispatchEvent::TYPE_ACCEPTED => [__('Worker accepted'), 'info', 'heroicon-m-hand-thumb-up'],
                DispatchEvent::TYPE_REJECTED => [__('Worker declined'), 'danger', 'heroicon-m-hand-thumb-down'],
                DispatchEvent::TYPE_EXPIRED => [__('Offer expired'), 'warning', 'heroicon-m-clock'],
                DispatchEvent::TYPE_ASSIGNED => [__('Worker assigned'), 'primary', 'heroicon-m-user-plus'],
                DispatchEvent::TYPE_REASSIGNED => [__('Worker unassigned'), 'warning', 'heroicon-m-arrows-right-left'],
                default => [__(ucfirst(str_replace('_', ' ', $event->type))), 'gray', 'heroicon-m-bolt'],
            };

            $push($event->created_at, $title, $icon, $color, collect([
                $workers[$event->worker_id] ?? null,
                $event->reason ? __(ucfirst(str_replace('_', ' ', $event->reason))) : null,
            ])->filter()->implode(' · ') ?: null,
                actor: match ($event->actor) {
                    'worker' => __('Worker'),
                    'admin' => __('Admin'),
                    default => __('System'),
                });
        }

        // --- Review --------------------------------------------------------
        $review = AppointmentReview::where('appointment_id', $appointment->id)->first();
        if ($review) {
            $push($review->created_at, __('Reviewed'), 'heroicon-m-star', 'warning',
                str_repeat('★', $review->rating).str_repeat('☆', AppointmentReview::MAX_RATING - $review->rating)
                .($review->comment ? ' — '.$review->comment : ''),
                actor: $appointment->customer?->name ? __('Customer').' · '.$appointment->customer->name : null);
        }

        // The slot itself, so the history reads against the promise: work that
        // started after this line started late. Still to come on an open job.
        $open = in_array($appointment->status, Appointment::ACTIVE_STATUSES, true);
        $upcoming = $open && $appointment->scheduled_at?->isFuture();
        $push($appointment->scheduled_at, __('Scheduled time'), 'heroicon-m-calendar-days', 'primary',
            $upcoming ? __('Upcoming') : null, planned: $upcoming);

        // Stable: events stamped in the same second keep the order they were
        // pushed in, which follows the booking's own flow.
        return $events->sortBy(fn (array $e, int $i) => [$e['at']->getTimestamp(), $i])->values();
    }

    /**
     * One action often saves the order twice — the admin cancel sets the
     * status, then marks the refund. Same person, same moment: one entry.
     *
     * @return Collection<int, AppointmentActivity>
     */
    private static function mergeBursts(Collection $activities): Collection
    {
        $merged = collect();

        foreach ($activities as $activity) {
            $previous = $merged->last();

            $sameMoment = $previous
                && $previous->event !== AppointmentActivity::EVENT_CREATED
                && $activity->event !== AppointmentActivity::EVENT_CREATED
                && $previous->actor_type === $activity->actor_type
                && $previous->actor_id === $activity->actor_id
                && abs($previous->created_at->diffInSeconds($activity->created_at)) <= 1
                // Two status moves are two steps, however quickly they came.
                && ! (isset($previous->changes['status']) && isset($activity->changes['status']));

            if (! $sameMoment) {
                $merged->push(clone $activity);

                continue;
            }

            $changes = $previous->changes ?? [];
            foreach ($activity->changes ?? [] as $field => [$old, $new]) {
                $changes[$field] = [$changes[$field][0] ?? $old, $new];
            }
            $previous->changes = $changes;
        }

        return $merged;
    }

    /** "Admin · Hani", "Customer · Sara", "System · payment gateway". */
    private static function actor(AppointmentActivity $activity): string
    {
        $role = match ($activity->actor_type) {
            AppointmentActivity::ACTOR_ADMIN => __('Admin'),
            AppointmentActivity::ACTOR_CUSTOMER => __('Customer'),
            AppointmentActivity::ACTOR_WORKER => __('Worker'),
            default => __('System'),
        };

        return filled($activity->actor_name) ? $role.' · '.__($activity->actor_name) : $role;
    }

    private static function statusLabel(?string $status): string
    {
        return $status ? __(ucwords(str_replace('_', ' ', $status))) : '-';
    }

    /**
     * Raw {field: [old, new]} into labelled, readable pairs.
     *
     * @return list<array{0: string, 1: ?string, 2: ?string}>
     */
    private static function describe(array $changes, Collection $workers): array
    {
        $rows = [];

        foreach ($changes as $field => [$old, $new]) {
            $format = match ($field) {
                'worker_id' => fn ($v) => $v === null ? __('Unassigned') : ($workers[$v] ?? '#'.$v),
                'customer_id' => fn ($v) => $v === null ? null : (Customer::find($v)?->name ?? '#'.$v),
                'scheduled_at' => fn ($v) => $v ? Carbon::parse($v)->translatedFormat('j M Y, H:i') : null,
                'payment_status', 'payment_method' => fn ($v) => $v ? __(ucfirst(str_replace('_', ' ', $v))) : null,
                'base_price', 'addons_total', 'discount_total', 'total_price' => fn ($v) => $v === null ? null : number_format((float) $v, 2).' '.__('SAR'),
                'auto_dispatch', 'assignment_locked' => fn ($v) => $v ? __('Yes') : __('No'),
                default => fn ($v) => $v === null || $v === '' ? null : (string) $v,
            };

            $rows[] = [self::fieldLabel($field), $format($old), $format($new)];
        }

        return $rows;
    }

    private static function fieldLabel(string $field): string
    {
        return match ($field) {
            'worker_id' => __('Worker'),
            'customer_id' => __('Customer'),
            'scheduled_at' => __('Scheduled at'),
            'payment_status' => __('Payment status'),
            'payment_method' => __('Payment method'),
            'service_name' => __('Service'),
            'vehicle_label' => __('Vehicle'),
            'address_label' => __('Address'),
            'notes' => __('Notes'),
            'base_price' => __('Base price'),
            'addons_total' => __('Add-ons total'),
            'discount_total' => __('Discount'),
            'total_price' => __('Total'),
            'auto_dispatch' => __('Auto dispatch'),
            'assignment_locked' => __('Assignment locked'),
            default => __(ucfirst(str_replace('_', ' ', $field))),
        };
    }
}
