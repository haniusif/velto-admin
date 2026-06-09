<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AppointmentResource;
use App\Models\Appointment;
use App\Models\CustomerNotification;
use App\Models\PackageAddOn;
use App\Models\PaymentTransaction;
use App\Models\TimeSlot;
use App\Models\WalletTransaction;
use App\Models\WashPackage;
use App\Services\ARB\ArbGateway;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    private const WITH = ['washPackage', 'vehicle', 'timeSlot', 'area', 'zone'];

    public function __construct(private readonly ArbGateway $arb)
    {
    }

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
        $booking = $this->resolveBooking($customer, $data);

        if ($data['payment_method'] === 'wallet') {
            return $this->createWalletBooking($customer, $booking, $data);
        }

        return $this->createCardBooking($request, $customer, $booking, $data);
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

        $isCardPaid = $appointment->payment_method !== 'wallet'
            && $appointment->payment_status === 'paid';

        DB::transaction(function () use ($appointment, $isCardPaid) {
            $appointment->update([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            $this->releaseSlot($appointment->time_slot_id);

            // Wallet refund is an internal ledger entry; card refund is external (below).
            if ($appointment->payment_status === 'paid' && ! $isCardPaid) {
                $appointment->customer->walletTransactions()->create([
                    'kind' => WalletTransaction::KIND_REFUND,
                    'amount' => (float) $appointment->total_price,
                    'note' => "Refund — booking #{$appointment->id} cancelled",
                ]);
                $appointment->update(['payment_status' => 'refunded']);
            }
        });

        if ($isCardPaid) {
            $this->refundCard($appointment);
        }

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
                $this->releaseSlot($appointment->time_slot_id);
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

    // --- booking helpers -------------------------------------------------

    /**
     * Validate ownership/catalog and compute pricing + snapshots.
     *
     * @return array{vehicle:\App\Models\Vehicle, package:WashPackage, addOns:\Illuminate\Support\Collection, base:float, addonsTotal:float, total:float, location:array}
     */
    private function resolveBooking($customer, array $data): array
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

        $base = (float) $package->price;
        $addonsTotal = (float) $addOns->sum('extra_price');

        return [
            'vehicle' => $vehicle,
            'package' => $package,
            'addOns' => $addOns,
            'base' => $base,
            'addonsTotal' => $addonsTotal,
            'total' => round($base + $addonsTotal, 2),
            'location' => $data['location'] ?? [],
        ];
    }

    private function createWalletBooking($customer, array $b, array $data): JsonResponse
    {
        if ((float) $customer->wallet_balance < $b['total']) {
            throw ValidationException::withMessages([
                'payment_method' => ['Insufficient wallet balance.'],
            ]);
        }

        $appointment = DB::transaction(function () use ($customer, $b, $data) {
            $slot = $this->lockBookableSlot($data['time_slot_id']);

            $appointment = $customer->appointments()->create($this->attributes(
                $b, $slot, $data,
                status: Appointment::STATUS_CONFIRMED,
                paymentMethod: 'wallet',
                paymentStatus: 'paid',
            ));

            $tx = $customer->walletTransactions()->create([
                'kind' => WalletTransaction::KIND_BOOKING,
                'amount' => -$b['total'],
                'note' => "Booking #{$appointment->id} — {$b['package']->name}",
            ]);
            $appointment->update(['wallet_transaction_id' => $tx->id]);

            $slot->increment('booked_count');
            $this->notifyBooked($customer, $appointment);

            return $appointment;
        });

        $appointment->load(self::WITH);

        return response()->json([
            'data' => [
                'appointment' => new AppointmentResource($appointment),
                'payment' => ['method' => 'wallet', 'status' => 'paid', 'payment_page_url' => null],
            ],
        ], 201);
    }

    private function createCardBooking(Request $request, $customer, array $b, array $data): JsonResponse
    {
        if (! $this->arb->isConfigured()) {
            throw ValidationException::withMessages([
                'payment_method' => ['Card payment is not available yet. Please pay with your wallet.'],
            ]);
        }

        // Reserve the slot and create the booking as pending before redirecting.
        [$appointment, $payment] = DB::transaction(function () use ($customer, $b, $data) {
            $slot = $this->lockBookableSlot($data['time_slot_id']);

            $appointment = $customer->appointments()->create($this->attributes(
                $b, $slot, $data,
                status: Appointment::STATUS_PENDING,
                paymentMethod: $data['payment_method'],
                paymentStatus: 'pending',
            ));
            $slot->increment('booked_count');

            $payment = PaymentTransaction::create([
                'customer_id' => $customer->id,
                'appointment_id' => $appointment->id,
                'gateway' => 'arb',
                'action' => 'purchase',
                'status' => PaymentTransaction::STATUS_PENDING,
                'amount' => $b['total'],
                'currency' => 'SAR',
                'track_id' => (string) $appointment->id,
            ]);

            return [$appointment, $payment];
        });

        try {
            $token = $this->arb->createPurchaseToken([
                'amount' => $b['total'],
                'track_id' => $payment->track_id,
                'response_url' => $this->callbackUrl('callback'),
                'error_url' => $this->callbackUrl('error'),
                'lang' => $customer->preferred_language,
                'customer_ip' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Compensate: free the slot and void the pending booking/payment.
            DB::transaction(function () use ($appointment, $payment, $e) {
                $this->releaseSlot($appointment->time_slot_id);
                $appointment->update([
                    'status' => Appointment::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ]);
                $payment->update([
                    'status' => PaymentTransaction::STATUS_FAILED,
                    'error_text' => mb_substr($e->getMessage(), 0, 250),
                ]);
            });
            Log::warning('ARB token generation failed', ['appointment' => $appointment->id, 'error' => $e->getMessage()]);

            return response()->json([
                'message' => 'Could not start card payment. Please try again.',
            ], 502);
        }

        $payment->update(['payment_id' => $token['payment_id']]);
        $appointment->load(self::WITH);

        return response()->json([
            'data' => [
                'appointment' => new AppointmentResource($appointment),
                'payment' => [
                    'method' => $data['payment_method'],
                    'status' => 'pending',
                    'payment_page_url' => $token['payment_url'],
                ],
            ],
        ], 201);
    }

    /** Build the appointment column set shared by wallet and card flows. */
    private function attributes(array $b, TimeSlot $slot, array $data, string $status, string $paymentMethod, string $paymentStatus): array
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
            'total_price' => $b['total'],
            'payment_method' => $paymentMethod,
            'payment_status' => $paymentStatus,
            'notes' => $data['notes'] ?? null,
        ];
    }

    /** Lock a bookable slot row and validate it; throws on unavailable/full/past. */
    private function lockBookableSlot(int $slotId): TimeSlot
    {
        $slot = TimeSlot::where('is_active', true)->lockForUpdate()->find($slotId);

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

        return $slot;
    }

    private function releaseSlot(?int $slotId): void
    {
        if (! $slotId) {
            return;
        }
        $slot = TimeSlot::lockForUpdate()->find($slotId);
        if ($slot && $slot->booked_count > 0) {
            $slot->decrement('booked_count');
        }
    }

    private function refundCard(Appointment $appointment): void
    {
        $payment = PaymentTransaction::where('appointment_id', $appointment->id)
            ->where('status', PaymentTransaction::STATUS_CAPTURED)
            ->latest('id')
            ->first();

        if (! $payment || ! $payment->trans_id || ! $this->arb->isConfigured()) {
            $appointment->update(['payment_status' => 'refund_pending']);

            return;
        }

        try {
            $res = $this->arb->refund($payment->trans_id, (float) $appointment->total_price, $payment->track_id);
            if ($res['success']) {
                $payment->update(['status' => PaymentTransaction::STATUS_REFUNDED]);
                $appointment->update(['payment_status' => 'refunded']);

                return;
            }
        } catch (\Throwable $e) {
            Log::warning('ARB refund failed', ['appointment' => $appointment->id, 'error' => $e->getMessage()]);
        }

        // Couldn't auto-refund — flag for manual handling via the merchant portal.
        $appointment->update(['payment_status' => 'refund_pending']);
    }

    private function callbackUrl(string $type): string
    {
        $base = rtrim((string) config('services.arb.callback_base'), '/');

        return "{$base}/api/v1/payments/arb/{$type}";
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
