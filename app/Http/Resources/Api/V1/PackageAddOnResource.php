<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class PackageAddOnResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'extra_price' => (float) $this->extra_price,
            'currency' => 'SAR',
            'icon_url' => $this->icon
                ? Storage::disk('public')->url($this->icon)
                : null,
            'sort_order' => (int) $this->sort_order,
        ];
    }
}
