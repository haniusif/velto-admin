<?php

namespace App\Jobs;

use App\Models\AssignmentOffer;
use App\Services\Dispatch\DispatchService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Delayed by the acceptance timeout. Expires the offer (and re-dispatches) if
 * the worker never responded. No-ops if the offer was already accepted/rejected.
 */
class ExpireOffer implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly int $offerId) {}

    public function handle(DispatchService $dispatch): void
    {
        $offer = AssignmentOffer::find($this->offerId);
        if ($offer !== null && $offer->isPending()) {
            $dispatch->expireOffer($offer);
        }
    }
}
