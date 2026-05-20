<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class CustomerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'area' => $this->area,
            'gender' => $this->gender,
            'preferred_language' => $this->preferred_language,
            'avatar_url' => $this->avatar_url
                ? Storage::disk('public')->url($this->avatar_url)
                : null,
            'profile_completed' => (bool) $this->profile_completed,
            'status' => $this->status,
            'joined_at' => optional($this->joined_at)?->toIso8601String(),
        ];
    }
}
