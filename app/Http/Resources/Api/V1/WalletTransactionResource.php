<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WalletTransactionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'kind' => $this->kind, // top_up | booking | refund | adjustment
            'amount' => (float) $this->amount,
            'currency' => 'SAR',
            'note' => $this->note,
            'at' => optional($this->created_at)?->toIso8601String(),
        ];
    }
}
