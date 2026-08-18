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
    /**
     * The real instant a time slot's wall-clock date + start_time refers to.
     *
     * Use this for comparisons ONLY — never for the value written to
     * scheduled_at, which is stored naively by design (see the class comment).
     *
     * Carbon::parse() with no timezone reads those digits as UTC, so a 18:00
     * Riyadh slot was compared as 18:00 UTC — three hours later than it really
     * is. Every slot therefore stayed "in the future" for three hours after it
     * had passed: it kept appearing in the picker, and the booking and
     * reschedule endpoints both accepted it.
     */
    public static function slotInstant(string $date, string $startTime): CarbonInterface
    {
        return Carbon::parse("{$date} {$startTime}", config('app.business_timezone'));
    }

    /**
     * A wall-clock time written the way a customer reads it: "2026-08-15 12:17 AM",
     * or "2026-08-15 12:17 ص" in Arabic.
     *
     * Notification bodies are composed once and stored, so the recipient's
     * language has to be decided here rather than in the app. Both apps show
     * a 12-hour clock; a body reading "00:17" beside a card reading "12:17 ص"
     * is the same booking twice in two dialects.
     *
     * Formats the stored digits and never converts: these are already Riyadh
     * wall clock (see the class comment), so a timezone shift here would move
     * every notification three hours.
     */
    public static function wallClockLabel(?CarbonInterface $wallClock, bool $arabic): ?string
    {
        if ($wallClock === null) {
            return null;
        }

        $period = (int) $wallClock->format('G') < 12
            ? ($arabic ? 'ص' : 'AM')
            : ($arabic ? 'م' : 'PM');

        // 'g' drops the leading zero on the hour, 'i' keeps it on the minutes.
        return $wallClock->format('Y-m-d g:i').' '.$period;
    }

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
