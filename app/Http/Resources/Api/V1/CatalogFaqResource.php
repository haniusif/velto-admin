<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CatalogFaqResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'question' => $this->question,
            'question_ar' => $this->question_ar,
            'answer' => $this->answer,
            'answer_ar' => $this->answer_ar,
        ];
    }
}
