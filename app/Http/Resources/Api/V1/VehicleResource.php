<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class VehicleResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'brand' => $this->brand,
            'model' => $this->model,
            'color' => $this->color,
            'plate' => $this->plate,
            'photo_url' => $this->photo_url
                ? (str_starts_with($this->photo_url, 'http')
                    ? $this->photo_url
                    : Storage::disk('public')->url($this->photo_url))
                : null,
            'is_default' => (bool) $this->is_default,
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
