<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;

/**
 * Serialises a booking's wall-clock time with the offset it was meant in.
 *
 * `scheduled_at` is built from a time slot's date and start_time, so it is a
 * wall-clock time in Riyadh — a customer choosing 21:00 means 21:00 there. It
 * is stored naively, and the app timezone is UTC, so toIso8601String() emitted
 * "21:00:00+00:00". Every client then converted that to local time and showed
 * the booking three hours late: a 21:00 wash appeared as 00:00 the next day.
 *
 * Reinterpreting the same digits in the business timezone gives
 * "21:00:00+03:00", which renders as 21:00 for anyone in Saudi Arabia and
 * converts correctly for anyone outside it.
 *
 * This is only for wall-clock values. Event timestamps like accepted_at are
 * genuine instants recorded with now() and are correctly UTC already.
 */
final class BookingTime
{
    public static function toIso(?CarbonInterface $wallClock): ?string
    {
        if ($wallClock === null) {
            return null;
        }

        // Take the calendar digits as-is and label them with the business
        // offset. shiftTimezone() moves the label without moving the clock;
        // setTimezone() would convert the instant and change the digits.
        return Carbon::parse($wallClock->format('Y-m-d H:i:s'), config('app.business_timezone'))
            ->toIso8601String();
    }
}
