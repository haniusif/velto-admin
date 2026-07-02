<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * An appointment as seen by the assigned worker. Unlike the customer's
 * AppointmentResource this exposes the customer's contact details (so the
 * worker can reach them) and the job-action flags, and omits pricing/payment.
 */
class WorkerJobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Appointment $this */
        return [
            'id' => $this->id,
            'status' => $this->status,
            'is_active' => in_array($this->status, Appointment::ACTIVE_STATUSES, true),
            'scheduled_at' => optional($this->scheduled_at)?->toIso8601String(),

            'service' => [
                'id' => $this->wash_package_id,
                'name' => $this->service_name,
                'name_ar' => $this->service_name_ar,
                'type' => $this->whenLoaded('washPackage', fn () => $this->washPackage?->type),
                'duration_minutes' => $this->whenLoaded(
                    'washPackage',
                    fn () => (int) ($this->washPackage?->duration_minutes ?? 0)
                ),
            ],

            'vehicle' => [
                'id' => $this->vehicle_id,
                'label' => $this->vehicle_label,
                'brand' => $this->whenLoaded('vehicle', fn () => $this->vehicle?->brand),
                'model' => $this->whenLoaded('vehicle', fn () => $this->vehicle?->model),
                'plate' => $this->whenLoaded('vehicle', fn () => $this->vehicle?->plate),
            ],

            'customer' => $this->whenLoaded('customer', fn () => $this->customer ? [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'phone' => $this->customer->phone,
            ] : null),

            'location' => [
                'label' => $this->address_label,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
                'area' => $this->whenLoaded('area', fn () => $this->area ? [
                    'id' => $this->area->id,
                    'name' => $this->area->name,
                    'name_ar' => $this->area->name_ar,
                ] : null),
                'zone' => $this->whenLoaded('zone', fn () => $this->zone ? [
                    'id' => $this->zone->id,
                    'name' => $this->zone->name,
                    'name_ar' => $this->zone->name_ar,
                ] : null),
            ],

            'time_slot' => $this->whenLoaded('timeSlot', fn () => $this->timeSlot ? [
                'id' => $this->timeSlot->id,
                'date' => optional($this->timeSlot->date)?->toDateString(),
                'start_time' => $this->timeSlot->start_time,
                'end_time' => $this->timeSlot->end_time,
            ] : null),

            'add_ons' => $this->add_ons ?? [],

            'notes' => $this->notes,

            // What the worker can do next, computed from the lifecycle state.
            'actions' => [
                'can_accept' => $this->resource->workerCanAccept(),
                'can_start' => $this->resource->workerCanStart(),
                'can_arrive' => $this->resource->workerCanArrive(),
                'can_start_work' => $this->resource->workerCanStartWork(),
                'can_complete' => $this->resource->workerCanComplete(),
            ],

            'accepted_at' => optional($this->accepted_at)?->toIso8601String(),
            'started_at' => optional($this->started_at)?->toIso8601String(),
            'arrived_at' => optional($this->arrived_at)?->toIso8601String(),
            'work_started_at' => optional($this->work_started_at)?->toIso8601String(),
            'completed_at' => optional($this->completed_at)?->toIso8601String(),
            'cancelled_at' => optional($this->cancelled_at)?->toIso8601String(),
        ];
    }
}
