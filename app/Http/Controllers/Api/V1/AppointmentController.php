<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AppointmentResource;
use App\Models\Appointment;
use App\Models\CustomerNotification;
use App\Models\PackageAddOn;
use App\Models\TimeSlot;
use App\Models\WalletTransaction;
use App\Models\WashPackage;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    private const WITH = ['washPackage', 'vehicle', 'timeSlot', 'area', 'zone'];

    /** GET /api/v1/me/appointments?filter=upcoming|past|all */
    public function index(Request $request): JsonResponse
    {
        $filter = $request->query('filter', 'all');

        $query = $request->user()->appointments()->with(self::WITH);

        if ($filter === 'upcoming') {
            $query->whereIn('status', Appointment::ACTIVE_STATUSES)->orderBy('scheduled_at');
        } elseif ($filter === 'past') {
            $query->whereNotIn('status', Appointment::ACTIVE_STATUSES)->orderByDesc('scheduled_at');
        } else {
            $query->orderByDesc('scheduled_at');
        }

        return response()->json(['data' => AppointmentResource::collection($query->get())]);
    }

    /** GET /api/v1/me/appointments/{appointment} */
    public function show(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwn($request, $appointment);
        $appointment->load(self::WITH);

        return response()->json(['data' => new AppointmentResource($appointment)]);
    }

    /** POST /api/v1/me/appointments */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'integer'],
            'wash_package_id' => ['required', 'integer'],
            'time_slot_id' => ['required', 'integer'],
            'add_on_ids' => ['nullable', 'array'],
            'add_on_ids.*' => ['integer'],
            'payment_method' => ['required', 'string', 'in:wallet,card,apple_pay'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'array'],
            'location.label' => ['nullable', 'string', 'max:255'],
            'location.lat' => ['nullable', 'numeric'],
            'location.lng' => ['nullable', 'numeric'],
            'location.area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'location.zone_id' => ['nullable', 'integer', 'exists:zones,id'],
        ]);

        $customer = $request->user();

        // Card / Apple Pay flows depend on the Neoleap gateway, which is not yet
        // configured. Reject cleanly rather than fabricate a payment.
        if ($data['payment_method'] !== 'wallet') {
            throw ValidationException::withMessages([
                'payment_method' => ['Card payment is not available yet. Please pay with your wallet.'],
            ]);
        }

        $vehicle = $customer->vehicles()->find($data['vehicle_id']);
        if (! $vehicle) {
            throw ValidationException::withMessages([
                'vehicle_id' => ['Vehicle not found.'],
            ]);
        }

        $package = WashPackage::where('is_active', true)->find($data['wash_package_id']);
        if (! $package) {
            throw ValidationException::withMessages([
                'wash_package_id' => ['Service not found.'],
            ]);
        }

        // Add-ons must belong to the chosen package and be active.
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

        $basePrice = (float) $package->price;
        $addonsTotal = (float) $addOns->sum('extra_price');
        $total = round($basePrice + $addonsTotal, 2);

        if ((float) $customer->wallet_balance < $total) {
            throw ValidationException::withMessages([
                'payment_method' => ['Insufficient wallet balance.'],
            ]);
        }

        $location = $data['location'] ?? [];

        $appointment = DB::transaction(function () use (
            $customer, $vehicle, $package, $addOns, $basePrice, $addonsTotal, $total, $location, $data
        ) {
            // Lock the slot row to settle capacity races.
            $slot = TimeSlot::where('is_active', true)
                ->lockForUpdate()
                ->find($data['time_slot_id']);

            if (! $slot) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['This time slot is no longer available.'],
                ]);
            }

            $scheduledAt = Carbon::parse($slot->date->toDateString().' '.$slot->start_time);

            if ($scheduledAt->isPast()) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['This time slot is in the past.'],
                ]);
            }

            if ($slot->booked_count >= $slot->capacity) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['This time slot is fully booked.'],
                ]);
            }

            $appointment = $customer->appointments()->create([
                'vehicle_id' => $vehicle->id,
                'wash_package_id' => $package->id,
                'time_slot_id' => $slot->id,
                'status' => Appointment::STATUS_CONFIRMED,
                'scheduled_at' => $scheduledAt,
                'address_label' => $location['label'] ?? null,
                'latitude' => $location['lat'] ?? null,
                'longitude' => $location['lng'] ?? null,
                'area_id' => $location['area_id'] ?? null,
                'zone_id' => $location['zone_id'] ?? null,
                'service_name' => $package->name,
                'service_name_ar' => $package->name_ar,
                'vehicle_label' => trim("{$vehicle->brand} {$vehicle->model}").
                    ($vehicle->plate ? " · {$vehicle->plate}" : ''),
                'add_ons' => $addOns->map(fn (PackageAddOn $a) => [
                    'id' => $a->id,
                    'name' => $a->name,
                    'name_ar' => $a->name_ar,
                    'extra_price' => (float) $a->extra_price,
                ])->values()->all(),
                'base_price' => $basePrice,
                'addons_total' => $addonsTotal,
                'total_price' => $total,
                'payment_method' => 'wallet',
                'payment_status' => 'paid',
                'notes' => $data['notes'] ?? null,
            ]);

            // Charge the wallet (negative amount; model trigger updates the balance).
            $tx = $customer->walletTransactions()->create([
                'kind' => WalletTransaction::KIND_BOOKING,
                'amount' => -$total,
                'note' => "Booking #{$appointment->id} — {$package->name}",
            ]);
            $appointment->update(['wallet_transaction_id' => $tx->id]);

            $slot->increment('booked_count');

            $this->notifyBooked($customer, $appointment);

            return $appointment;
        });

        $appointment->load(self::WITH);

        return response()->json(['data' => new AppointmentResource($appointment)], 201);
    }

    /** POST /api/v1/me/appointments/{appointment}/cancel */
    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwn($request, $appointment);

        if (! $appointment->isActionable()) {
            throw ValidationException::withMessages([
                'appointment' => ['This booking can no longer be cancelled.'],
            ]);
        }

        DB::transaction(function () use ($appointment) {
            $appointment->update([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            // Free the slot.
            if ($appointment->time_slot_id) {
                $slot = TimeSlot::lockForUpdate()->find($appointment->time_slot_id);
                if ($slot && $slot->booked_count > 0) {
                    $slot->decrement('booked_count');
                }
            }

            // Refund a wallet-paid booking.
            if ($appointment->payment_status === 'paid') {
                $appointment->customer->walletTransactions()->create([
                    'kind' => WalletTransaction::KIND_REFUND,
                    'amount' => (float) $appointment->total_price,
                    'note' => "Refund — booking #{$appointment->id} cancelled",
                ]);
                $appointment->update(['payment_status' => 'refunded']);
            }
        });

        $appointment->load(self::WITH);

        return response()->json(['data' => new AppointmentResource($appointment)]);
    }

    /** PATCH /api/v1/me/appointments/{appointment}/reschedule */
    public function reschedule(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwn($request, $appointment);

        $data = $request->validate([
            'time_slot_id' => ['required', 'integer'],
        ]);

        if (! $appointment->isActionable()) {
            throw ValidationException::withMessages([
                'appointment' => ['This booking can no longer be rescheduled.'],
            ]);
        }

        DB::transaction(function () use ($appointment, $data) {
            $newSlot = TimeSlot::where('is_active', true)
                ->lockForUpdate()
                ->find($data['time_slot_id']);

            if (! $newSlot) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['This time slot is no longer available.'],
                ]);
            }

            $scheduledAt = Carbon::parse($newSlot->date->toDateString().' '.$newSlot->start_time);

            if ($scheduledAt->isPast()) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['This time slot is in the past.'],
                ]);
            }

            if ($newSlot->id !== $appointment->time_slot_id
                && $newSlot->booked_count >= $newSlot->capacity) {
                throw ValidationException::withMessages([
                    'time_slot_id' => ['This time slot is fully booked.'],
                ]);
            }

            if ($newSlot->id !== $appointment->time_slot_id) {
                if ($appointment->time_slot_id) {
                    $oldSlot = TimeSlot::lockForUpdate()->find($appointment->time_slot_id);
                    if ($oldSlot && $oldSlot->booked_count > 0) {
                        $oldSlot->decrement('booked_count');
                    }
                }
                $newSlot->increment('booked_count');
            }

            $appointment->update([
                'time_slot_id' => $newSlot->id,
                'scheduled_at' => $scheduledAt,
            ]);
        });

        $appointment->load(self::WITH);

        return response()->json(['data' => new AppointmentResource($appointment)]);
    }

    private function notifyBooked($customer, Appointment $appointment): void
    {
        $when = $appointment->scheduled_at?->format('Y-m-d H:i');

        $customer->customerNotifications()->create([
            'kind' => CustomerNotification::KIND_BOOKING,
            'title' => 'Booking confirmed',
            'title_ar' => 'تم تأكيد الحجز',
            'body' => "{$appointment->service_name} on {$when}",
            'body_ar' => "{$appointment->service_name} بتاريخ {$when}",
            'data' => ['appointment_id' => $appointment->id],
        ]);
    }

    private function authorizeOwn(Request $request, Appointment $appointment): void
    {
        abort_unless($appointment->customer_id === $request->user()?->id, 404);
    }
}
