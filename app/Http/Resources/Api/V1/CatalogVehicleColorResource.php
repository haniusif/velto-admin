<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogVehicleColorResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'hex' => $this->hex,
            'is_light_swatch' => (bool) $this->is_light_swatch,
        ];
    }
}
