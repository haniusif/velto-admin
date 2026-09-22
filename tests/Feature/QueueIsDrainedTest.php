<?php

namespace Tests\Feature;

use Illuminate\Console\Scheduling\Schedule;
use Tests\TestCase;

/**
 * The database queue had no consumer at all: no daemon (proc_open is disabled
 * on the host) and nothing in the schedule. Every ShouldQueue job enqueued
 * since July sat untouched. A missing consumer fails silently by nature, so
 * pin it here rather than waiting to notice the next pile-up.
 */
class QueueIsDrainedTest extends TestCase
{
    public function test_the_schedule_drains_the_queue_every_minute(): void
    {
        $events = collect(app(Schedule::class)->events());

        $queueWork = $events->first(fn ($e) => $e->description === 'queue-work');

        $this->assertNotNull($queueWork, 'Nothing in the schedule consumes the queue.');
        $this->assertSame('* * * * *', $queueWork->expression);
    }

    public function test_the_dispatch_backstop_still_runs(): void
    {
        $events = collect(app(Schedule::class)->events());

        $this->assertNotNull(
            $events->first(fn ($e) => $e->description === 'dispatch-sweep'),
            'dispatch:sweep is the backstop that kept bookings assigned while the queue was dead.',
        );
    }
}
