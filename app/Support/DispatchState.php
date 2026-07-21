<?php

namespace App\Support;

/**
 * The dispatch lifecycle — how a job got (or is getting) a worker. Sits above
 * the appointment `status`, which continues to track the job's own progress.
 */
final class DispatchState
{
    public const SCHEDULED = 'scheduled';           // future booking; dispatch deferred to lead time
    public const WAITING = 'waiting';               // no eligible worker; queued for retry
    public const AUTO_ASSIGNING = 'auto_assigning'; // strategy resolving a candidate
    public const OFFERED = 'offered';               // offer sent, countdown live
    public const ASSIGNED = 'assigned';             // worker committed (accepted/manual/locked)
    public const EN_ROUTE = 'en_route';             // worker travelling (job status on_the_way/arrived)
    public const WORKING = 'working';               // job in progress
    public const COMPLETED = 'completed';
    public const CANCELLED = 'cancelled';

    /** States from which the engine may still (re)dispatch a job. */
    public const REDISPATCHABLE = [
        self::WAITING,
        self::AUTO_ASSIGNING,
        self::OFFERED,
        self::ASSIGNED,
    ];
}
