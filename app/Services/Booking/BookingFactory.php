<?php

namespace App\Services\Booking;

use App\Models\PackageAddOn;
use App\Models\PromoCode;
use App\Models\TimeSlot;
use App\Models\Vehicle;
use App\Models\WashPackage;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

/**
 * Turning a booking request into the rows an appointment needs.
 *
 * Extracted so buying a plan can create its first visit through exactly the
 * same validation and pricing as an ordinary booking — two code paths that
 * disagreed about what a valid slot or a correct total is would be a very
 * expensive bug.
 */
class BookingFactory
{
    public function resolveBooking($customer, array $data): array
    {
        $vehicle = $customer->vehicles()->find($data['vehicle_id']);
        if (! $vehicle) {
            throw ValidationException::withMessages(['vehicle_id' => ['Vehicle not found.']]);
        }

        $package = WashPackage::where('is_active', true)->find($data['wash_package_id']);
        if (! $package) {
            throw ValidationException::withMessages(['wash_package_id' => ['Service not found.']]);
        }

        $addOns = collect();
        if (! empty($data['add_on_ids'])) {
            $addOns = PackageAddOn::where('wash_package_id', $package->id)
                ->where('is_active', true)
                ->whereIn('id', $data['add_on_ids'])
                ->get();

            if ($addOns->count() !== count(array_unique($data['add_on_ids']))) {
                throw ValidationException::withMessages([
                    'add_on_ids' => ['One or more add-ons are invalid for this service.'],
                ]);
            }
        }

        $base = $this->basePriceFor($vehicle, $package);
        $addonsTotal = (float) $addOns->sum('extra_price');
        $subtotal = round($base + $addonsTotal, 2);

        // A promo code is resolved here so pricing has exactly one home: the
        // quote the customer is shown and the row that gets written come from
        // the same arithmetic.
        $promo = null;
        $discount = 0.0;
        if (! empty($data['promo_code'])) {
            $promo = PromoCode::findByCode($data['promo_code']);

            if (! $promo) {
                throw ValidationException::withMessages([
                    'promo_code' => [PromoCode::REASON_NOT_FOUND],
                ]);
            }

            $reason = $promo->rejectionReason($customer->id, $subtotal);
            if ($reason !== null) {
                throw ValidationException::withMessages(['promo_code' => [$reason]]);
            }

            $discount = $promo->discountFor($subtotal);
        }

        return [
            'vehicle' => $vehicle,
            'package' => $package,
            'addOns' => $addOns,
            'base' => $base,
            'addonsTotal' => $addonsTotal,
            'promo' => $promo,
            'discount' => $discount,
            'total' => round($subtotal - $discount, 2),
            'location' => $data['location'] ?? [],
        ];
    }

    /**
     * What the wash itself costs, before add-ons and promos.
     *
     * Priced by the size of the car: a Large costs more to wash than a Small,
     * whatever the service. The wash package's own price is the fallback, used
     * when the car can't be placed in a band — free-typed model, unclassified
     * model, or a band with no price set. Falling back to the package price
     * keeps an unrecognised car bookable at the advertised rate instead of
     * failing the booking or pricing it at zero.
     */
    private function basePriceFor(Vehicle $vehicle, WashPackage $package): float
    {
        $categoryPrice = $vehicle->sizeCategory()?->price;

        return round((float) ($categoryPrice ?? $package->price), 2);
    }

    public function attributes(array $b, TimeSlot $slot, array $data, string $status, string $paymentMethod, string $paymentStatus): array
    {
        $vehicle = $b['vehicle'];
        $location = $b['location'];

        return [
            'vehicle_id' => $vehicle->id,
            'wash_package_id' => $b['package']->id,
            'time_slot_id' => $slot->id,
            'status' => $status,
            'scheduled_at' => Carbon::parse($slot->date->toDateString().' '.$slot->start_time),
            'address_label' => $location['label'] ?? null,
            'latitude' => $location['lat'] ?? null,
            'longitude' => $location['lng'] ?? null,
            'area_id' => $location['area_id'] ?? null,
            'zone_id' => $location['zone_id'] ?? null,
            'service_name' => $b['package']->name,
            'service_name_ar' => $b['package']->name_ar,
            'vehicle_label' => trim("{$vehicle->brand} {$vehicle->model}").
                ($vehicle->plate ? " · {$vehicle->plate}" : ''),
            'add_ons' => $b['addOns']->map(fn (PackageAddOn $a) => [
                'id' => $a->id,
                'name' => $a->name,
                'name_ar' => $a->name_ar,
                'extra_price' => (float) $a->extra_price,
            ])->values()->all(),
            'base_price' => $b['base'],
            'addons_total' => $b['addonsTotal'],
            'discount_total' => $b['discount'] ?? 0,
            'promo_code_id' => isset($b['promo']) ? $b['promo']?->id : null,
            'total_price' => $b['total'],
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'notes' => $data['notes'] ?? null,
        ];
    }

    /** Lock a bookable slot row and validate it; throws on unavailable/full/past. */
    public function lockBookableSlot(int $slotId): TimeSlot
    {
        $slot = TimeSlot::where('is_active', true)->lockForUpdate()->find($slotId);

        if (! $slot) {
            throw ValidationException::withMessages([
                'time_slot_id' => ['This time slot is no longer available.'],
            ]);
        }

        // scheduled_at itself stays naive wall-clock; only the check is
        // timezone-aware, or a slot that passed up to three hours ago is
        // still accepted.
        if (BookingTime::slotInstant($slot->date->toDateString(), $slot->start_time)->isPast()) {
            throw ValidationException::withMessages([
                'time_slot_id' => ['This time slot is in the past.'],
            ]);
        }

        if ($slot->booked_count >= $slot->capacity) {
            throw ValidationException::withMessages([
                'time_slot_id' => ['This time slot is fully booked.'],
            ]);
        }

        return $slot;
    }
}
