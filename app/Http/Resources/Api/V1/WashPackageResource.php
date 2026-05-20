<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class WashPackageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'description' => $this->description,
            'description_ar' => $this->description_ar,
            'type' => $this->type, // single | multi
            'price' => (float) $this->price,
            'currency' => 'SAR',
            'duration_minutes' => (int) $this->duration_minutes,
            'visits_count' => $this->when($this->type === 'multi', fn () => (int) $this->visits_count),
            'validity_days' => $this->when($this->type === 'multi', fn () => (int) $this->validity_days),
            'image_url' => $this->image_path
                ? Storage::disk('public')->url($this->image_path)
                : null,
            'is_featured' => (bool) $this->is_featured,
            'sort_order' => (int) $this->sort_order,
            'add_ons' => PackageAddOnResource::collection($this->whenLoaded('addOns')),
        ];
    }
}
