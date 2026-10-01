<?php

namespace App\Services\Notifications;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Filament\Resources\Customers\CustomerResource;
use App\Jobs\SendAdminAlert;
use App\Models\Appointment;
use App\Models\AppointmentActivity;
use App\Models\Customer;
use App\Models\User;
use App\Models\Worker;
use App\Services\Dispatch\DispatchSettings;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification as LaravelNotification;
use Throwable;

/**
 * Tells the team what is happening without them watching the panel: a new
 * booking, one that needs a worker, a cancellation, a worker's progress, a
 * new customer. Each alert goes to the panel bell, email and/or SMS as set
 * on the Admin alerts page.
 *
 * Fed by the order change log, so every alert knows who did what. Alerts
 * never throw: a failed SMS must not fail the booking that caused it.
 */
class AdminAlerts
{
    private const LOOK = [
        'booking_confirmed' => ['heroicon-o-calendar-days', 'success'],
        'needs_worker' => ['heroicon-o-exclamation-triangle', 'warning'],
        'booking_cancelled' => ['heroicon-o-x-circle', 'danger'],
        'booking_rescheduled' => ['heroicon-o-arrow-path', 'info'],
        'worker_assigned' => ['heroicon-o-user-plus', 'primary'],
        'worker_on_the_way' => ['heroicon-o-truck', 'warning'],
        'worker_arrived' => ['heroicon-o-map-pin', 'warning'],
        'job_started' => ['heroicon-o-wrench-screwdriver', 'primary'],
        'job_completed' => ['heroicon-o-check-badge', 'success'],
        'customer_registered' => ['heroicon-o-user', 'info'],
    ];

    public function __construct(private readonly AdminAlertSettings $settings) {}

    /** Called for every change-log row. */
    public static function fromActivity(AppointmentActivity $activity, Appointment $appointment): void
    {
        self::safely(fn () => app(self::class)->forActivity($activity, $appointment));
    }

    public static function customerRegistered(Customer $customer): void
    {
        self::safely(fn () => app(self::class)->send('customer_registered',
            ['name' => $customer->name ?: $customer->phone, 'phone' => $customer->phone],
            CustomerResource::getUrl('view', ['record' => $customer->id], panel: 'admin')));
    }

    /** No worker could be found, or nobody will look for one. */
    public static function needsWorker(Appointment $appointment, string $reason): void
    {
        self::safely(fn () => app(self::class)->send('needs_worker',
            self::orderVars($appointment) + ['reason' => $reason],
            self::orderUrl($appointment)));
    }

    private function forActivity(AppointmentActivity $activity, Appointment $appointment): void
    {
        $changes = $activity->changes ?? [];
        $vars = self::orderVars($appointment) + ['actor' => self::actorName($activity)];
        $url = self::orderUrl($appointment);
        $actorUserId = $activity->actor_type === AppointmentActivity::ACTOR_ADMIN ? $activity->actor_id : null;

        [$from, $to] = $activity->event === AppointmentActivity::EVENT_CREATED
            ? [null, $appointment->status]
            : ($changes['status'] ?? [null, null]);

        if ($to === Appointment::STATUS_CONFIRMED) {
            $this->send('booking_confirmed', $vars, $url, $actorUserId);

            // With auto-assign off nothing will pick a worker; say so now
            // rather than when the slot arrives with nobody coming.
            if ($appointment->worker_id === null && ! app(DispatchSettings::class)->autoDispatchEnabled()) {
                $this->send('needs_worker', $vars + ['reason' => __('Auto-assign is off', [], $this->settings->language())], $url);
            }
        }

        // pending → cancelled is a card payment abandoned or timed out — the
        // booking never really existed, so it is noise, not news.
        if ($to === Appointment::STATUS_CANCELLED && $from !== Appointment::STATUS_PENDING) {
            $this->send('booking_cancelled', $vars, $url, $actorUserId);
        }

        $tracking = [
            Appointment::STATUS_ON_THE_WAY => 'worker_on_the_way',
            Appointment::STATUS_ARRIVED => 'worker_arrived',
            Appointment::STATUS_IN_PROGRESS => 'job_started',
            Appointment::STATUS_COMPLETED => 'job_completed',
        ];
        if (isset($tracking[$to])) {
            $this->send($tracking[$to], $vars, $url, $actorUserId);
        }

        if (isset($changes['worker_id']) && $changes['worker_id'][1] !== null) {
            $this->send('worker_assigned', $vars, $url, $actorUserId);
        }

        if (isset($changes['scheduled_at'])) {
            $this->send('booking_rescheduled', $vars + [
                'old' => self::when($changes['scheduled_at'][0], $this->settings->language()),
            ], $url, $actorUserId);
        }
    }

    /**
     * @param  array<string,string|null>  $vars
     * @param  int|null  $exceptUserId  the admin who did it already knows
     */
    private function send(string $event, array $vars, string $url, ?int $exceptUserId = null): void
    {
        $lang = $this->settings->language();
        $vars = array_map(fn ($v) => filled($v) ? (string) $v : '-', $vars);

        [$title, $body] = AdminAlertMessages::render($event, $vars, $lang);

        if ($this->settings->enabled($event, AdminAlertSettings::CHANNEL_BELL)) {
            $recipients = User::query()->whereHas('roles')
                ->when($exceptUserId, fn ($q) => $q->whereKeyNot($exceptUserId))
                ->get();

            if ($recipients->isNotEmpty()) {
                [$icon, $color] = self::LOOK[$event] ?? ['heroicon-o-bell', 'gray'];

                // sendNow, not sendToDatabase: Filament queues the latter, and
                // the queue here only drains once a minute.
                $bell = Notification::make()
                    ->title($title)
                    ->body($body)
                    ->icon($icon)
                    ->iconColor($color)
                    ->actions([
                        Action::make('open')->label(__('Open', [], $lang))->url($url)->markAsRead(),
                    ]);

                LaravelNotification::sendNow($recipients, $bell->toDatabase());
            }
        }

        $emails = $this->settings->enabled($event, AdminAlertSettings::CHANNEL_EMAIL) ? $this->settings->emails() : [];
        $phones = $this->settings->enabled($event, AdminAlertSettings::CHANNEL_SMS) ? $this->settings->phones() : [];

        if ($emails !== [] || $phones !== []) {
            // Queued: an SMS gateway taking seconds to answer must not hold
            // up the customer's booking or the worker's tap.
            SendAdminAlert::dispatch($title, $body, $url, $emails, $phones)->afterCommit();
        }
    }

    /** @return array<string,?string> */
    private static function orderVars(Appointment $appointment): array
    {
        $lang = app(AdminAlertSettings::class)->language();

        return [
            'id' => (string) $appointment->id,
            'customer' => $appointment->customer?->name ?: $appointment->customer?->phone,
            'service' => $lang === 'ar' && $appointment->service_name_ar ? $appointment->service_name_ar : $appointment->service_name,
            'when' => self::when($appointment->scheduled_at, $lang),
            'total' => number_format((float) $appointment->total_price, 2).' '.__('SAR', [], $lang),
            'worker' => $appointment->worker_id ? Worker::whereKey($appointment->worker_id)->value('name') : null,
            'address' => $appointment->address_label,
        ];
    }

    private static function when(mixed $value, string $lang): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        return \Illuminate\Support\Carbon::parse($value)->locale($lang)->translatedFormat('D j M, H:i');
    }

    private static function orderUrl(Appointment $appointment): string
    {
        return AppointmentResource::getUrl('view', ['record' => $appointment->id], panel: 'admin');
    }

    private static function actorName(AppointmentActivity $activity): string
    {
        $lang = app(AdminAlertSettings::class)->language();
        $role = match ($activity->actor_type) {
            AppointmentActivity::ACTOR_ADMIN => __('Admin', [], $lang),
            AppointmentActivity::ACTOR_CUSTOMER => __('Customer', [], $lang),
            AppointmentActivity::ACTOR_WORKER => __('Worker', [], $lang),
            default => __('System', [], $lang),
        };

        return filled($activity->actor_name) ? $role.' · '.__($activity->actor_name, [], $lang) : $role;
    }

    private static function safely(callable $fn): void
    {
        try {
            $fn();
        } catch (Throwable $e) {
            Log::error('[alerts] could not raise an admin alert', ['error' => $e->getMessage()]);
        }
    }
}
