<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\CustomerPackage */
class CustomerPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            // `status` is the effective one: a plan whose window lapsed reads as
            // expired even if no sweep has rewritten the row yet.
            'status' => $this->resource->effectiveStatus(),
            'is_usable' => $this->resource->isUsable(),

            'visits_total' => $this->visits_total,
            'visits_used' => $this->visits_used,
            'visits_remaining' => $this->resource->visitsRemaining(),

            'price_paid' => (float) $this->price_paid,
            'currency' => 'SAR',
            'payment_method' => $this->payment_method,
            'payment_status' => $this->payment_status,

            'starts_at' => optional($this->starts_at)?->toIso8601String(),
            'expires_at' => optional($this->expires_at)?->toIso8601String(),

            'wash_package' => new WashPackageResource($this->whenLoaded('washPackage')),
            'vehicle' => new VehicleResource($this->whenLoaded('vehicle')),

            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
