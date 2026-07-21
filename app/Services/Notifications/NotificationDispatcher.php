<?php

namespace App\Services\Notifications;

use App\Models\Appointment;
use App\Models\AssignmentOffer;
use App\Models\CustomerNotification;
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
        if ($a->customer_id === null) {
            return;
        }
        $name = $a->worker?->name ?? '';

        CustomerNotification::create([
            'customer_id' => $a->customer_id,
            'kind' => $changed ? CustomerNotification::KIND_WORKER_CHANGED : CustomerNotification::KIND_WORKER_ASSIGNED,
            'title' => $changed ? 'Your specialist changed' : 'Your specialist is confirmed',
            'title_ar' => $changed ? 'تم تغيير الأخصائي' : 'تم تأكيد الأخصائي',
            'body' => trim($name),
            'body_ar' => trim($name),
            'data' => ['appointment_id' => $a->id, 'worker_id' => $a->worker_id],
        ]);
    }

    // --- internals -------------------------------------------------------

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

        // Push is dormant until a device-token store + FCM key exist (Phase 1
        // ships DB-only). The seam is here so it lights up without touching callers.
        $this->push->send([], $title, $body, $data);
    }
}
