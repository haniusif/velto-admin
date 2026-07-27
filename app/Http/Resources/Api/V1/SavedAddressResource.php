<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\SavedAddress */
class SavedAddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'label' => $this->label,
            'subtitle' => $this->subtitle ?? '',
            'lat' => (float) $this->lat,
            'lng' => (float) $this->lng,
            'is_covered' => (bool) $this->is_covered,
            'icon_key' => $this->icon_key,
        ];
    }
}
