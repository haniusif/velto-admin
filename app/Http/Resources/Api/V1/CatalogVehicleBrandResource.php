<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CatalogVehicleBrandResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'icon_url' => $this->icon_path
                ? (str_starts_with($this->icon_path, 'http')
                    ? $this->icon_path
                    : Storage::disk('public')->url($this->icon_path))
                : null,
            'models' => CatalogVehicleModelResource::collection($this->whenLoaded('models')),
        ];
    }
}
