<?php

namespace Tests\Feature;

use App\Models\TimeSlot;
use App\Support\BookingTime;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A time slot's date + start_time are Riyadh wall-clock digits stored naively.
 * Reading them back with Carbon::parse() interprets them as UTC, three hours
 * earlier than they mean — so every slot stayed "in the future" for three
 * hours after it had passed. It kept appearing in the picker, and both the
 * booking and reschedule endpoints accepted it.
 */
class PastSlotTimezoneTest extends TestCase
{
    use RefreshDatabase;

    private function riyadhNow(): CarbonImmutable
    {
        return CarbonImmutable::now(config('app.business_timezone'));
    }

    private function slotAt(CarbonImmutable $riyadhMoment, int $capacity = 5): TimeSlot
    {
        return TimeSlot::create([
            'date' => $riyadhMoment->toDateString(),
            'start_time' => $riyadhMoment->format('H:i:s'),
            'end_time' => $riyadhMoment->addHour()->format('H:i:s'),
            'capacity' => $capacity,
            'booked_count' => 0,
            'is_active' => true,
        ]);
    }

    public function test_a_slot_that_passed_an_hour_ago_is_in_the_past(): void
    {
        $moment = $this->riyadhNow()->subHour();

        $instant = BookingTime::slotInstant($moment->toDateString(), $moment->format('H:i:s'));

        $this->assertTrue($instant->isPast(), 'an hour-old slot must read as past');
    }

    public function test_a_naive_parse_now_agrees_with_the_explicit_one(): void
    {
        // This used to document the defect: while the app ran on UTC, parsing
        // a slot's digits without naming a zone read them three hours early,
        // so an hour-old slot still looked like the future.
        //
        // The app now runs on Riyadh, so the two readings have converged and
        // the disagreement is gone. slotInstant() is kept because it states
        // which zone the digits mean instead of inheriting it, which is what
        // makes the next timezone change safe rather than silent.
        $moment = $this->riyadhNow()->subHour();
        $naive = CarbonImmutable::parse($moment->toDateString().' '.$moment->format('H:i:s'));
        $explicit = BookingTime::slotInstant($moment->toDateString(), $moment->format('H:i:s'));

        $this->assertTrue($naive->isPast(), 'an hour-old slot must read as past');
        $this->assertTrue($explicit->isPast());
        $this->assertSame($explicit->format('Y-m-d H:i'), $naive->format('Y-m-d H:i'));
    }

    public function test_availability_hides_a_slot_that_has_already_started(): void
    {
        $past = $this->slotAt($this->riyadhNow()->subHour());
        $future = $this->slotAt($this->riyadhNow()->addHours(4));

        $ids = collect($this->getJson('/api/v1/catalog/availability')->json('data'))
            ->pluck('id');

        $this->assertNotContains($past->id, $ids, 'a slot that has started is still offered');
        $this->assertContains($future->id, $ids, 'a later slot today must stay bookable');
    }

    public function test_a_slot_later_today_is_still_offered(): void
    {
        // Guards the over-correction: shifting the comparison the wrong way
        // would hide today's remaining slots entirely.
        $soon = $this->slotAt($this->riyadhNow()->addMinutes(90));

        $ids = collect($this->getJson('/api/v1/catalog/availability')->json('data'))
            ->pluck('id');

        $this->assertContains($soon->id, $ids);
    }

    public function test_slot_instant_keeps_the_wall_clock_digits(): void
    {
        // The stored value must stay naive; only the comparison is offset-aware.
        $instant = BookingTime::slotInstant('2026-08-16', '21:00:00');

        $this->assertSame('21:00', $instant->format('H:i'));
        $this->assertSame('+03:00', $instant->format('P'));
    }
}
