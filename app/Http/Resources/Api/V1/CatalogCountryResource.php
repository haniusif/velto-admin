<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogCountryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'code' => $this->code,
            'dial' => $this->dial,
            'flag' => $this->flag,
            'name' => $this->name,
            'name_ar' => $this->name_ar,
            'phone_length' => (int) $this->phone_length,
            'is_default' => (bool) $this->is_default,
        ];
    }
}
