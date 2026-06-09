<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TimeSlotResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $available = max(0, (int) $this->capacity - (int) $this->booked_count);

        return [
            'id' => $this->id,
            'date' => optional($this->date)?->toDateString(),
            'start_time' => substr((string) $this->start_time, 0, 5),
            'end_time' => substr((string) $this->end_time, 0, 5),
            'capacity' => (int) $this->capacity,
            'available' => $available,
            'is_full' => $available <= 0,
        ];
    }
}
