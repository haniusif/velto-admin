<?php

namespace Tests\Unit;

use App\Models\WorkerLocation;
use Tests\TestCase;

/**
 * Whether a position is fresh enough to show a customer.
 *
 * A stale dot is worse than no dot: it asserts "your specialist is here" when
 * they may have moved on, so the boundary is worth pinning.
 */
class WorkerLocationTest extends TestCase
{
    private function at(?\DateTimeInterface $updatedAt): WorkerLocation
    {
        $l = new WorkerLocation;
        $l->updated_at = $updatedAt;

        return $l;
    }

    public function test_a_position_from_moments_ago_is_fresh(): void
    {
        $this->assertTrue($this->at(now()->subSeconds(30))->isFresh());
    }

    public function test_a_position_inside_the_window_is_fresh(): void
    {
        $window = WorkerLocation::FRESH_SECONDS;

        $this->assertTrue($this->at(now()->subSeconds($window - 5))->isFresh());
    }

    public function test_a_position_past_the_window_is_stale(): void
    {
        $window = WorkerLocation::FRESH_SECONDS;

        $this->assertFalse($this->at(now()->subSeconds($window + 5))->isFresh());
    }

    /**
     * Carbon 3 returns a SIGNED difference. Without abs(), a timestamp that
     * looks like it is in the future — clock skew on the worker's device, or a
     * timezone mishap — sails through the check and a stale position is shown
     * as live. This caught exactly that.
     */
    public function test_a_future_timestamp_is_not_treated_as_fresh(): void
    {
        $this->assertFalse($this->at(now()->addHours(3))->isFresh(),
            'a signed diff would make a future timestamp look current');
    }

    public function test_a_position_that_was_never_written_is_not_fresh(): void
    {
        $this->assertFalse($this->at(null)->isFresh());
    }
}
