<?php

namespace App\Services\Notifications;

use App\Models\Appointment;
use App\Models\AssignmentOffer;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Worker;
use App\Models\WorkerNotification;
use App\Support\BookingTime;

/**
 * One place that turns a dispatch event into notifications, fanning each out to
 * the right recipients over the right channels. Today: the durable DB inbox
 * (Worker/CustomerNotification) plus a config-gated push (dormant until FCM +
 * device tokens exist). Realtime/websocket is a later channel behind this seam.
 */
class NotificationDispatcher
{
    public function __construct(private readonly PushSender $push) {}

    /** Worker (re)assigned to a job — the notification the old model hook sent. */
    public function workerAssigned(Appointment $a): void
    {
        $when = BookingTime::wallClockLabel($a->scheduled_at, arabic: false);
        $whenAr = BookingTime::wallClockLabel($a->scheduled_at, arabic: true);
        $serviceAr = $a->service_name_ar ?: $a->service_name;

        $this->toWorker($a->worker_id, WorkerNotification::KIND_ASSIGNED,
            'New job assigned', 'تم إسناد مهمة جديدة',
            trim("{$a->service_name} — {$when}"), trim("{$serviceAr} — {$whenAr}"),
            ['appointment_id' => $a->id]);
    }

    /** Worker offered a job with a live countdown (offer mode). */
    public function workerOffered(AssignmentOffer $offer): void
    {
        $a = $offer->appointment;
        $when = BookingTime::wallClockLabel($a?->scheduled_at, arabic: false);
        $whenAr = BookingTime::wallClockLabel($a?->scheduled_at, arabic: true);
        $serviceAr = $a?->service_name_ar ?: $a?->service_name;

        $this->toWorker($offer->worker_id, WorkerNotification::KIND_OFFERED,
            'New job offer', 'عرض مهمة جديدة',
            trim("{$a?->service_name} — {$when}"), trim("{$serviceAr} — {$whenAr}"),
            [
                'appointment_id' => $a?->id,
                'offer_id' => $offer->id,
                'expires_at' => $offer->expires_at?->toIso8601String(),
            ]);
    }

    /**
     * The customer cancelled a job that already had a worker on it. Without
     * this the job only disappears from their list on the next refresh, so a
     * specialist already on the way keeps driving to a cancelled booking.
     */
    public function workerJobCancelled(Appointment $a, ?int $workerId = null): void
    {
        $when = BookingTime::wallClockLabel($a->scheduled_at, arabic: false);
        $whenAr = BookingTime::wallClockLabel($a->scheduled_at, arabic: true);
        $serviceAr = $a->service_name_ar ?: $a->service_name;

        $this->toWorker($workerId ?? $a->worker_id, WorkerNotification::KIND_CANCELLED,
            'Job cancelled', 'تم إلغاء المهمة',
            trim("{$a->service_name} — {$when}"), trim("{$serviceAr} — {$whenAr}"),
            ['appointment_id' => $a->id]);
    }

    /** A worker lost a job to reassignment. */
    public function workerReassignedAway(int $workerId, Appointment $a): void
    {
        $this->toWorker($workerId, WorkerNotification::KIND_REASSIGNED_AWAY,
            'Job reassigned', 'تمت إعادة إسناد المهمة',
            "Order #{$a->id} was reassigned", "تمت إعادة إسناد الطلب رقم #{$a->id}",
            ['appointment_id' => $a->id]);
    }

    /** Customer's specialist is confirmed (or changed). */
    public function customerWorkerAssigned(Appointment $a, bool $changed = false): void
    {
        $name = $a->worker?->name ?? '';

        $this->toCustomer($a->customer_id,
            $changed ? CustomerNotification::KIND_WORKER_CHANGED : CustomerNotification::KIND_WORKER_ASSIGNED,
            $changed ? 'Your specialist changed' : 'Your specialist is confirmed',
            $changed ? 'تم تغيير الأخصائي' : 'تم تأكيد الأخصائي',
            trim($name), trim($name),
            ['appointment_id' => $a->id, 'worker_id' => $a->worker_id]);
    }

    /** Booking confirmed — after checkout or a successful payment capture. */
    public function customerBooked(Appointment $a): void
    {
        $when = BookingTime::wallClockLabel($a->scheduled_at, arabic: false);
        $whenAr = BookingTime::wallClockLabel($a->scheduled_at, arabic: true);
        // The Arabic body was built from the English service name, so an
        // Arabic customer's "booking confirmed" arrived half in English.
        $serviceAr = $a->service_name_ar ?: $a->service_name;

        $this->toCustomer($a->customer_id, CustomerNotification::KIND_BOOKING,
            'Booking confirmed', 'تم تأكيد الحجز',
            "{$a->service_name} on {$when}", "{$serviceAr} بتاريخ {$whenAr}",
            ['appointment_id' => $a->id]);
    }

    /** Job status moved on — on the way / arrived / completed. */
    public function customerJobStatus(
        Appointment $a, string $kind,
        string $title, string $titleAr, string $body, string $bodyAr,
    ): void {
        $this->toCustomer($a->customer_id, $kind, $title, $titleAr, $body, $bodyAr,
            ['appointment_id' => $a->id]);
    }

    /**
     * A hand-written notification from the admin, to one customer or many.
     *
     * Routes through the same path as every automatic notification, so a
     * broadcast lands in the in-app inbox *and* on the device, localized per
     * recipient. Returns how many customers were reached.
     *
     * @param  iterable<int>  $customerIds
     * @param  array<string,mixed>  $data
     */
    public function customerAnnouncement(
        iterable $customerIds, string $kind, string $title, string $titleAr,
        string $body, string $bodyAr, array $data = [],
    ): int {
        $sent = 0;

        foreach ($customerIds as $id) {
            $this->toCustomer($id, $kind, $title, $titleAr, $body, $bodyAr, $data);
            $sent++;
        }

        return $sent;
    }

    /**
     * Push an inbox row that already exists to the customer's devices, in the
     * language they picked. Separate from toCustomer() because the admin's own
     * create form writes the row itself — calling the full path there would
     * leave the customer with the same message in their inbox twice.
     *
     * Returns false when there is nothing to push to, so a caller can say so.
     */
    public function pushCustomerNotification(CustomerNotification $notification): bool
    {
        $customer = $notification->customer;

        if ($customer === null) {
            return false;
        }

        $tokens = $customer->deviceTokens();

        if ($tokens === []) {
            return false;
        }

        // Arabic is the app default, and the Arabic copy is optional on the
        // admin form — fall back rather than push an empty banner.
        $useAr = ($customer->preferred_language ?? 'ar') === 'ar';
        $title = $useAr ? ($notification->title_ar ?: $notification->title) : $notification->title;
        $body = $useAr ? ($notification->body_ar ?: $notification->body) : $notification->body;

        $this->push->send(
            $tokens,
            (string) $title,
            (string) $body,
            array_map('strval', array_merge($notification->data ?? [], ['kind' => $notification->kind])),
            PushSender::AUDIENCE_CUSTOMER,
        );

        return true;
    }

    // --- internals -------------------------------------------------------

    /**
     * Durable inbox row + a push to the customer's devices, in their language.
     * Mirrors toWorker(); the audience selects the Firebase project, service
     * account, and Android channel ('booking' for the customer app).
     */
    private function toCustomer(
        ?int $customerId, string $kind, string $title, string $titleAr,
        string $body, string $bodyAr, array $data
    ): void {
        if ($customerId === null) {
            return;
        }

        $notification = CustomerNotification::create([
            'customer_id' => $customerId,
            'kind' => $kind,
            'title' => $title,
            'title_ar' => $titleAr,
            'body' => $body,
            'body_ar' => $bodyAr,
            'data' => $data,
        ]);

        $this->pushCustomerNotification($notification);
    }

    private function toWorker(
        ?int $workerId, string $kind, string $title, string $titleAr,
        string $body, string $bodyAr, array $data
    ): void {
        if ($workerId === null) {
            return;
        }

        WorkerNotification::create([
            'worker_id' => $workerId,
            'kind' => $kind,
            'title' => $title,
            'title_ar' => $titleAr,
            'body' => $body,
            'body_ar' => $bodyAr,
            'data' => $data,
        ]);

        // Fan the same notification out as an FCM push to the worker's devices.
        // Send the worker's preferred-language title/body so the banner is
        // localized; PushSender no-ops when FCM isn't configured.
        $worker = Worker::find($workerId);
        if ($worker !== null) {
            $useAr = ($worker->preferred_language ?? 'ar') === 'ar';
            $this->push->send(
                $worker->deviceTokens(),
                $useAr ? $titleAr : $title,
                $useAr ? $bodyAr : $body,
                array_map('strval', array_merge($data, ['kind' => $kind])),
                PushSender::AUDIENCE_WORKER,
            );
        }
    }
}
