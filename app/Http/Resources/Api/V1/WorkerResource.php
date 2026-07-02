<?php

namespace App\Http\Resources\Api\V1;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class WorkerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        /** @var Worker $this */
        return [
            'id' => $this->id,
            'name' => $this->name,
            'phone' => $this->phone,
            'email' => $this->email,
            'city' => $this->city,
            'status' => $this->status,
            'rating' => $this->rating !== null ? (float) $this->rating : null,
            'preferred_language' => $this->preferred_language,
            'avatar_url' => $this->avatar_url
                ? Storage::disk('public')->url($this->avatar_url)
                : null,
            'hire_date' => optional($this->hire_date)?->toDateString(),
        ];
    }
}
