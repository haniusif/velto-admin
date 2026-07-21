<?php

namespace App\Services\Dispatch\Strategies;

use App\Models\Appointment;
use App\Models\Worker;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Cycle through eligible workers by id so assignments spread evenly regardless
 * of current load. The cursor (last-assigned id) lives in the cache.
 */
class RoundRobinStrategy implements AssignmentStrategy
{
    private const CURSOR = 'dispatch.rr_cursor';

    public function pick(Appointment $appointment, Collection $eligible): ?Worker
    {
        if ($eligible->isEmpty()) {
            return null;
        }

        $sorted = $eligible->sortBy('id')->values();
        $cursor = (int) Cache::get(self::CURSOR, 0);

        $picked = $sorted->first(fn (Worker $w) => $w->id > $cursor) ?? $sorted->first();
        Cache::put(self::CURSOR, $picked->id, now()->addDay());

        return $picked;
    }

    public function key(): string
    {
        return self::ROUND_ROBIN;
    }
}
