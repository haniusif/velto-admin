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
            // The size band this car prices off. Null when the model was typed
            // free-hand or isn't classified yet — the client must then fall
            // back to the service's own price, exactly as the server does.
            'category' => $this->sizeCategoryPayload(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }

    private function sizeCategoryPayload(): ?array
    {
        $category = $this->resource->sizeCategory();

        if (! $category || $category->price === null) {
            return null;
        }

        return [
            'code' => $category->code,
            'name' => $category->name,
            'name_ar' => $category->name_ar,
            'price' => (float) $category->price,
            'currency' => 'SAR',
        ];
    }
}
