<?php

namespace Tests\Feature;

use App\Models\PromoCode;
use App\Support\BookingTime;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * A promo code's window is typed in the admin as Riyadh wall clock and stored
 * naively, but the app runs in UTC — so comparing the cast value against now()
 * put every code three hours out at both ends. A code set to expire at 22:39
 * stayed redeemable until 01:39 the next morning, and one set to start at
 * 09:00 could not be used until noon.
 */
class PromoWindowTimezoneTest extends TestCase
{
    use RefreshDatabase;

    private function riyadhNow(): CarbonImmutable
    {
        return CarbonImmutable::now(config('app.business_timezone'));
    }

    /** Stores the digits exactly as the admin form writes them: naive. */
    private function code(?CarbonImmutable $startsAt, ?CarbonImmutable $expiresAt): PromoCode
    {
        return PromoCode::create([
            'code' => 'WINDOW',
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 10,
            'min_order_total' => 0,
            'per_customer_limit' => 1,
            'used_count' => 0,
            'is_active' => true,
            'starts_at' => $startsAt?->format('Y-m-d H:i:s'),
            'expires_at' => $expiresAt?->format('Y-m-d H:i:s'),
        ]);
    }

    public function test_a_code_that_expired_an_hour_ago_is_not_redeemable(): void
    {
        // Inside the three-hour offset, which is where the bug lived.
        $code = $this->code(null, $this->riyadhNow()->subHour());

        $this->assertFalse($code->withinWindow(), 'an expired code is still redeemable');
    }

    public function test_the_naive_comparison_that_caused_the_bug_disagrees(): void
    {
        // Documents the defect rather than the fix: the old comparison called
        // an hour-expired code "still live". If this ever stops disagreeing,
        // the timezones have converged and the fix is moot.
        $expired = $this->riyadhNow()->subHour();
        $naive = Carbon::parse($expired->format('Y-m-d H:i:s'));

        $this->assertTrue($naive->isFuture(), 'the naive read must still look future-dated');
        $this->assertTrue(BookingTime::instant($naive)->isPast());
    }

    public function test_a_code_that_started_an_hour_ago_is_redeemable_now(): void
    {
        // The other end: a code was dead for three hours after going live.
        $code = $this->code($this->riyadhNow()->subHour(), $this->riyadhNow()->addDay());

        $this->assertTrue($code->withinWindow(), 'a started code is not usable yet');
    }

    public function test_a_code_that_starts_in_an_hour_is_not_redeemable_yet(): void
    {
        // Guards the over-correction: shifting the wrong way would open every
        // future code three hours early.
        $code = $this->code($this->riyadhNow()->addHour(), $this->riyadhNow()->addDay());

        $this->assertFalse($code->withinWindow());
    }

    public function test_a_code_expiring_in_an_hour_is_still_redeemable(): void
    {
        $code = $this->code(null, $this->riyadhNow()->addHour());

        $this->assertTrue($code->withinWindow());
    }

    public function test_an_open_ended_code_is_redeemable(): void
    {
        $this->assertTrue($this->code(null, null)->withinWindow());
    }

    public function test_an_inactive_code_stays_closed_whatever_the_window(): void
    {
        $code = $this->code(null, $this->riyadhNow()->addDay());
        $code->update(['is_active' => false]);

        $this->assertFalse($code->fresh()->withinWindow());
    }

    public function test_the_expiry_reason_reaches_the_app(): void
    {
        // The API answers with a reason constant, so the customer is told the
        // code expired rather than being handed a silent failure.
        $code = $this->code(null, $this->riyadhNow()->subHour());

        $this->assertSame(PromoCode::REASON_EXPIRED, $code->rejectionReason(1, 100.0));
    }

    public function test_now_wall_clock_carries_the_business_offset(): void
    {
        // What the SQL filter binds. Its digits must read as Riyadh so they
        // line up with the naive column being compared.
        $now = BookingTime::nowWallClock();

        $this->assertSame('+03:00', $now->format('P'));
        $this->assertSame($this->riyadhNow()->format('Y-m-d H:i'), $now->format('Y-m-d H:i'));
    }

    public function test_to_iso_still_labels_wall_clock_with_the_business_offset(): void
    {
        // toIso() now delegates to instant(); this pins that it kept behaving.
        $iso = BookingTime::toIso(Carbon::parse('2026-08-19 21:00:00'));

        $this->assertSame('2026-08-19T21:00:00+03:00', $iso);
        $this->assertNull(BookingTime::toIso(null));
    }
}
