<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\AppointmentReview;
use App\Models\DispatchEvent;
use App\Models\PaymentTransaction;
use App\Models\Worker;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

/**
 * Everything that happened to one booking, oldest first: its own status
 * stamps, each payment attempt, every dispatch decision and the review.
 *
 * Each source already exists — the admin just had to open four screens and
 * line the times up by hand to answer "why was this job late?".
 */
class AppointmentTimeline
{
    /**
     * @return Collection<int, array{at: CarbonInterface, title: string, detail: ?string, icon: string, color: string, planned: bool}>
     */
    public static function for(Appointment $appointment): Collection
    {
        $events = collect();

        $push = function (?CarbonInterface $at, string $title, string $icon, string $color, ?string $detail = null, bool $planned = false) use ($events): void {
            if ($at !== null) {
                $events->push(compact('at', 'title', 'detail', 'icon', 'color', 'planned'));
            }
        };

        $push($appointment->created_at, __('Booked'), 'heroicon-m-plus', 'gray',
            $appointment->customer?->name);

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

        $dispatch = DispatchEvent::where('appointment_id', $appointment->id)->orderBy('id')->get();
        $workers = Worker::whereIn('id', $dispatch->pluck('worker_id')->filter()->unique())->pluck('name', 'id');

        foreach ($dispatch as $event) {
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
                $event->actor && $event->actor !== 'engine' ? __('by :actor', ['actor' => __(ucfirst($event->actor))]) : null,
                $event->reason ? __(ucfirst(str_replace('_', ' ', $event->reason))) : null,
            ])->filter()->implode(' · ') ?: null);
        }

        $worker = $appointment->worker?->name;

        $push($appointment->accepted_at, __('Accepted'), 'heroicon-m-check', 'info', $worker);
        $push($appointment->started_at, __('On the way'), 'heroicon-m-truck', 'warning', $worker);
        $push($appointment->arrived_at, __('Arrived'), 'heroicon-m-map-pin', 'warning');
        $push($appointment->work_started_at, __('Work started'), 'heroicon-m-wrench-screwdriver', 'primary');
        $push($appointment->completed_at, __('Completed'), 'heroicon-m-check-badge', 'success');
        $push($appointment->cancelled_at, __('Cancelled'), 'heroicon-m-x-mark', 'danger');

        $review = AppointmentReview::where('appointment_id', $appointment->id)->first();
        if ($review) {
            $push($review->created_at, __('Reviewed'), 'heroicon-m-star', 'warning',
                str_repeat('★', $review->rating).str_repeat('☆', AppointmentReview::MAX_RATING - $review->rating)
                .($review->comment ? ' — '.$review->comment : ''));
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
}
