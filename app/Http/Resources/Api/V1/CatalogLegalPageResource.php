<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogLegalPageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'title_ar' => $this->title_ar,
            'body' => $this->body,
            'body_ar' => $this->body_ar,
            'version' => $this->version,
            'updated_at' => optional($this->updated_at)?->toIso8601String(),
        ];
    }
}
