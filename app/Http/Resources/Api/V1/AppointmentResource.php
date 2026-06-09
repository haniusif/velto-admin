<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Appointment $this */
        return [
            'id' => $this->id,
            'status' => $this->status,
            'is_upcoming' => in_array($this->status, Appointment::ACTIVE_STATUSES, true),
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

            'base_price' => (float) $this->base_price,
            'addons_total' => (float) $this->addons_total,
            'total_price' => (float) $this->total_price,
            'currency' => 'SAR',

            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,

            'can_cancel' => $this->resource->isActionable(),
            'can_reschedule' => $this->resource->isActionable(),

            'notes' => $this->notes,
            'cancelled_at' => optional($this->cancelled_at)?->toIso8601String(),
            'completed_at' => optional($this->completed_at)?->toIso8601String(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
