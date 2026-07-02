<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\WorkerJobResource;
use App\Models\Appointment;
use App\Models\CustomerNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WorkerJobController extends Controller
{
    private const WITH = ['washPackage', 'vehicle', 'timeSlot', 'area', 'zone', 'customer'];

    /** GET /api/v1/worker/jobs?filter=today|upcoming|active|completed|all */
    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'active');

        $query = $request->user()->appointments()->with(self::WITH);

        switch ($filter) {
            case 'today':
                $query->whereIn('status', Appointment::ACTIVE_STATUSES)
                    ->whereDate('scheduled_at', today())
                    ->orderBy('scheduled_at');
                break;
            case 'upcoming':
                $query->whereIn('status', Appointment::ACTIVE_STATUSES)
                    ->where('scheduled_at', '>', now())
                    ->orderBy('scheduled_at');
                break;
            case 'completed':
                $query->where('status', Appointment::STATUS_COMPLETED)
                    ->orderByDesc('completed_at');
                break;
            case 'active':
                $query->whereIn('status', Appointment::ACTIVE_STATUSES)
                    ->orderBy('scheduled_at');
                break;
            default: // all
                $query->orderByDesc('scheduled_at');
        }

        return response()->json(['data' => WorkerJobResource::collection($query->get())]);
    }

    /** GET /api/v1/worker/jobs/{appointment} */
    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAssigned($request, $appointment);
        $appointment->load(self::WITH);

        return response()->json(['data' => new WorkerJobResource($appointment)]);
    }

    /** POST /api/v1/worker/jobs/{appointment}/accept */
    public function accept(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAssigned($request, $appointment);

        if (! $appointment->workerCanAccept()) {
            throw ValidationException::withMessages([
                'job' => ['This job can no longer be accepted.'],
            ]);
        }

        $appointment->update(['accepted_at' => now()]);

        return $this->respond($appointment);
    }

    /** POST /api/v1/worker/jobs/{appointment}/start */
    public function start(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAssigned($request, $appointment);

        if (! $appointment->workerCanStart()) {
            throw ValidationException::withMessages([
                'job' => ['This job cannot be started yet.'],
            ]);
        }

        $appointment->update([
            'status' => Appointment::STATUS_ON_THE_WAY,
            'started_at' => now(),
        ]);

        $this->notifyCustomer($appointment, CustomerNotification::KIND_ON_THE_WAY,
            'Your specialist is on the way', 'الفني في الطريق إليك',
            "{$appointment->service_name} — your specialist is heading to you.",
            "{$appointment->service_name} — الفني في طريقه إليك.");

        return $this->respond($appointment);
    }

    /** POST /api/v1/worker/jobs/{appointment}/arrived */
    public function arrived(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAssigned($request, $appointment);

        if (! $appointment->workerCanArrive()) {
            throw ValidationException::withMessages([
                'job' => ['This job cannot be marked as arrived.'],
            ]);
        }

        $appointment->update([
            'status' => Appointment::STATUS_ARRIVED,
            'arrived_at' => now(),
        ]);

        $this->notifyCustomer($appointment, CustomerNotification::KIND_ARRIVED,
            'Your specialist has arrived', 'وصل الفني',
            "{$appointment->service_name} — your specialist has arrived.",
            "{$appointment->service_name} — وصل الفني إليك.");

        return $this->respond($appointment);
    }

    /** POST /api/v1/worker/jobs/{appointment}/start-work */
    public function startWork(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAssigned($request, $appointment);

        if (! $appointment->workerCanStartWork()) {
            throw ValidationException::withMessages([
                'job' => ['This job cannot be started yet.'],
            ]);
        }

        $appointment->update([
            'status' => Appointment::STATUS_IN_PROGRESS,
            'work_started_at' => now(),
        ]);

        return $this->respond($appointment);
    }

    /** POST /api/v1/worker/jobs/{appointment}/complete */
    public function complete(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeAssigned($request, $appointment);

        if (! $appointment->workerCanComplete()) {
            throw ValidationException::withMessages([
                'job' => ['This job cannot be completed.'],
            ]);
        }

        $appointment->update([
            'status' => Appointment::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        $this->notifyCustomer($appointment, CustomerNotification::KIND_COMPLETED,
            'Service completed', 'تم إنجاز الخدمة',
            "{$appointment->service_name} is done. Thank you for choosing Velto!",
            "تم إنجاز {$appointment->service_name}. شكراً لاختيارك فيلتو!");

        return $this->respond($appointment);
    }

    // --- helpers ---------------------------------------------------------

    private function respond(Appointment $appointment): JsonResponse
    {
        $appointment->load(self::WITH);

        return response()->json(['data' => new WorkerJobResource($appointment)]);
    }

    private function notifyCustomer(
        Appointment $appointment,
        string $kind,
        string $title,
        string $titleAr,
        string $body,
        string $bodyAr,
    ): void {
        $appointment->customer?->customerNotifications()->create([
            'kind' => $kind,
            'title' => $title,
            'title_ar' => $titleAr,
            'body' => $body,
            'body_ar' => $bodyAr,
            'data' => ['appointment_id' => $appointment->id],
        ]);
    }

    private function authorizeAssigned(Request $request, Appointment $appointment): void
    {
        abort_unless($appointment->worker_id === $request->user()?->id, 404);
    }
}
