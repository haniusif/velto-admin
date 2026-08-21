<?php

namespace App\Support;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\CustomerPackage;
use Illuminate\Support\Facades\DB;

/**
 * The numbers behind a customer's profile: what they are worth, how reliable
 * they are, and when they were last seen.
 *
 * Gathered here rather than as a dozen entry-level closures so the page costs
 * a fixed handful of queries instead of one per figure, and so "cancellation
 * rate" means the same thing wherever it is shown. Memoised per customer
 * because an infolist evaluates each entry separately.
 *
 * @phpstan-type Totals array{bookings:int, completed:int, cancelled:int, upcoming:int, spend:float}
 */
final class CustomerProfile
{
    /** @var array<int, self> */
    private static array $cache = [];

    private function __construct(
        public readonly int $bookings,
        public readonly int $completed,
        public readonly int $cancelled,
        public readonly int $upcoming,
        public readonly float $spend,
        public readonly ?Appointment $lastVisit,
        public readonly ?Appointment $nextVisit,
        public readonly int $activePlans,
        public readonly int $promoRedemptions,
        public readonly float $promoDiscount,
    ) {}

    public static function for(Customer $customer): self
    {
        return self::$cache[$customer->id] ??= self::build($customer);
    }

    /** Drops the memo so a test (or a long-lived request) can recompute. */
    public static function forget(): void
    {
        self::$cache = [];
    }

    private static function build(Customer $customer): self
    {
        // One pass for every count and the revenue total. Only completed
        // bookings count as spend: a cancelled one was refunded, and a booking
        // still in the diary has not been earned yet.
        $totals = DB::table('appointments')
            ->where('customer_id', $customer->id)
            ->selectRaw('COUNT(*) AS bookings')
            ->selectRaw('SUM(status = ?) AS completed', [Appointment::STATUS_COMPLETED])
            ->selectRaw('SUM(status = ?) AS cancelled', [Appointment::STATUS_CANCELLED])
            ->selectRaw('SUM(status IN (?, ?)) AS upcoming', [
                Appointment::STATUS_PENDING,
                Appointment::STATUS_CONFIRMED,
            ])
            ->selectRaw('COALESCE(SUM(CASE WHEN status = ? THEN total_price ELSE 0 END), 0) AS spend', [
                Appointment::STATUS_COMPLETED,
            ])
            ->first();

        $promo = DB::table('promo_code_redemptions')
            ->where('customer_id', $customer->id)
            ->selectRaw('COUNT(*) AS uses, COALESCE(SUM(amount), 0) AS discount')
            ->first();

        return new self(
            bookings: (int) ($totals->bookings ?? 0),
            completed: (int) ($totals->completed ?? 0),
            cancelled: (int) ($totals->cancelled ?? 0),
            upcoming: (int) ($totals->upcoming ?? 0),
            spend: (float) ($totals->spend ?? 0),
            lastVisit: $customer->appointments()
                ->where('status', Appointment::STATUS_COMPLETED)
                ->latest('scheduled_at')
                ->first(),
            nextVisit: $customer->appointments()
                ->whereIn('status', [Appointment::STATUS_PENDING, Appointment::STATUS_CONFIRMED])
                ->orderBy('scheduled_at')
                ->first(),
            activePlans: $customer->packages()
                ->where('status', CustomerPackage::STATUS_ACTIVE)
                ->count(),
            promoRedemptions: (int) ($promo->uses ?? 0),
            promoDiscount: (float) ($promo->discount ?? 0),
        );
    }

    /** Average value of a completed visit, or null before there is one. */
    public function averageOrder(): ?float
    {
        return $this->completed > 0 ? round($this->spend / $this->completed, 2) : null;
    }

    /**
     * Share of this customer's bookings that ended cancelled.
     *
     * Measured against every booking they ever made, including the ones still
     * ahead of them — a customer with one cancellation and one booking in the
     * diary has cancelled half of what they started, and rounding that away
     * would read as flawless.
     */
    public function cancellationRate(): ?float
    {
        return $this->bookings > 0
            ? round($this->cancelled / $this->bookings * 100, 1)
            : null;
    }
}
