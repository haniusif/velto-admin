<?php

namespace App\Services\Notifications;

use App\Models\Appointment;
use App\Models\AssignmentOffer;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Worker;
use App\Models\WorkerNotification;

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
        $when = $a->scheduled_at?->format('Y-m-d H:i');
        $serviceAr = $a->service_name_ar ?: $a->service_name;

        $this->toWorker($a->worker_id, WorkerNotification::KIND_ASSIGNED,
            'New job assigned', 'تم إسناد مهمة جديدة',
            trim("{$a->service_name} — {$when}"), trim("{$serviceAr} — {$when}"),
            ['appointment_id' => $a->id]);
    }

    /** Worker offered a job with a live countdown (offer mode). */
    public function workerOffered(AssignmentOffer $offer): void
    {
        $a = $offer->appointment;
        $when = $a?->scheduled_at?->format('Y-m-d H:i');
        $serviceAr = $a?->service_name_ar ?: $a?->service_name;

        $this->toWorker($offer->worker_id, WorkerNotification::KIND_OFFERED,
            'New job offer', 'عرض مهمة جديدة',
            trim("{$a?->service_name} — {$when}"), trim("{$serviceAr} — {$when}"),
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
        $when = $a->scheduled_at?->format('Y-m-d H:i');
        $serviceAr = $a->service_name_ar ?: $a->service_name;

        $this->toWorker($workerId ?? $a->worker_id, WorkerNotification::KIND_CANCELLED,
            'Job cancelled', 'تم إلغاء المهمة',
            trim("{$a->service_name} — {$when}"), trim("{$serviceAr} — {$when}"),
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
        $when = $a->scheduled_at?->format('Y-m-d H:i');

        $this->toCustomer($a->customer_id, CustomerNotification::KIND_BOOKING,
            'Booking confirmed', 'تم تأكيد الحجز',
            "{$a->service_name} on {$when}", "{$a->service_name} بتاريخ {$when}",
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

        CustomerNotification::create([
            'customer_id' => $customerId,
            'kind' => $kind,
            'title' => $title,
            'title_ar' => $titleAr,
            'body' => $body,
            'body_ar' => $bodyAr,
            'data' => $data,
        ]);

        $customer = Customer::find($customerId);
        if ($customer !== null) {
            $useAr = ($customer->preferred_language ?? 'ar') === 'ar';
            $this->push->send(
                $customer->deviceTokens(),
                $useAr ? $titleAr : $title,
                $useAr ? $bodyAr : $body,
                array_map('strval', array_merge($data, ['kind' => $kind])),
                PushSender::AUDIENCE_CUSTOMER,
            );
        }
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
