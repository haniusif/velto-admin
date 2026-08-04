<?php

namespace Tests\Unit;

use App\Support\BookingTime;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * A booking of 21:00 was showing in the apps as 00:00 the following day.
 *
 * scheduled_at is built from a time slot's date and start_time, so it is a
 * wall-clock time in Riyadh. It is stored naively and the app timezone is UTC,
 * so it was serialised as "21:00:00+00:00" — every client dutifully converted
 * that to local time and moved the booking three hours late.
 */
class BookingTimeTest extends TestCase
{
    public function test_the_wall_clock_digits_survive_serialisation(): void
    {
        $stored = Carbon::parse('2026-08-03 21:00:00', 'UTC');

        // Same digits, correct offset — not shifted to 18:00 or 00:00.
        $this->assertSame('2026-08-03T21:00:00+03:00', BookingTime::toIso($stored));
    }

    public function test_it_labels_rather_than_converts(): void
    {
        $iso = BookingTime::toIso(Carbon::parse('2026-08-03 21:00:00', 'UTC'));

        $this->assertStringContainsString('21:00:00', $iso, 'the hour must not move');
        $this->assertStringEndsWith('+03:00', $iso, 'the offset must say Riyadh');
    }

    /**
     * The bug in one assertion: a client in Riyadh reading the old value.
     */
    public function test_a_riyadh_client_now_reads_back_the_booked_hour(): void
    {
        $stored = Carbon::parse('2026-08-03 21:00:00', 'UTC');

        $wrong = Carbon::parse($stored->toIso8601String())->setTimezone('Asia/Riyadh');
        $this->assertSame('2026-08-04 00:00', $wrong->format('Y-m-d H:i'), 'this was the bug');

        $right = Carbon::parse(BookingTime::toIso($stored))->setTimezone('Asia/Riyadh');
        $this->assertSame('2026-08-03 21:00', $right->format('Y-m-d H:i'));
    }

    public function test_midnight_does_not_slide_into_the_previous_day(): void
    {
        $stored = Carbon::parse('2026-08-04 00:00:00', 'UTC');

        $this->assertSame('2026-08-04T00:00:00+03:00', BookingTime::toIso($stored));
    }

    public function test_null_stays_null(): void
    {
        $this->assertNull(BookingTime::toIso(null));
    }

    public function test_it_follows_the_configured_business_timezone(): void
    {
        config()->set('app.business_timezone', 'UTC');

        $this->assertSame(
            '2026-08-03T21:00:00+00:00',
            BookingTime::toIso(Carbon::parse('2026-08-03 21:00:00', 'UTC')),
        );
    }
}
