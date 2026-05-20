<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CustomerNotificationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind, // booking | on_the_way | completed | promo
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'body' => $this->body,
            'body_ar' => $this->body_ar,
            'data' => $this->data,
            'is_read' => $this->read_at !== null,
            'read_at' => optional($this->read_at)?->toIso8601String(),
            'created_at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
