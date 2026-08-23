<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AppointmentResource;
use App\Http\Resources\Api\V1\CustomerPackageResource;
use App\Models\Appointment;
use App\Models\CustomerPackage;
use App\Models\PaymentTransaction;
use App\Models\WalletTransaction;
use App\Models\WashPackage;
use App\Services\ARB\ArbGateway;
use App\Services\Booking\BookingFactory;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Prepaid multi-visit plans. Buying one mirrors booking: wallet settles inline,
 * card returns a hosted-page URL and the plan only activates on capture.
 */
class CustomerPackageController extends Controller
{
    private const WITH = ['washPackage.addOns', 'vehicle'];

    public function __construct(
        private readonly ArbGateway $arb,
        private readonly BookingFactory $bookings,
        private readonly NotificationDispatcher $notifications,
    ) {}

    /** GET /api/v1/me/packages — the customer's plans, newest first. */
    public function index(Request $request): JsonResponse
    {
        $packages = $request->user()->packages()
            ->with(self::WITH)
            ->latest('id')
            ->get();

        return response()->json([
            'data' => CustomerPackageResource::collection($packages),
        ]);
    }

    /** POST /api/v1/me/packages — buy a plan for one vehicle. */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'wash_package_id' => ['required', 'integer'],
            'vehicle_id' => ['required', 'integer'],
            'payment_method' => ['required', 'string', 'in:wallet,card,apple_pay'],

            // Subscribing schedules the first visit in the same step, so the
            // customer never lands on an empty plan wondering what to do next.
            'first_booking' => ['nullable', 'array'],
            'first_booking.time_slot_id' => ['required_with:first_booking', 'integer'],
            'first_booking.add_on_ids' => ['nullable', 'array'],
            'first_booking.add_on_ids.*' => ['integer'],
            'first_booking.notes' => ['nullable', 'string', 'max:1000'],
            'first_booking.location' => ['nullable', 'array'],
            'first_booking.location.label' => ['nullable', 'string', 'max:255'],
            'first_booking.location.lat' => ['nullable', 'numeric'],
            'first_booking.location.lng' => ['nullable', 'numeric'],
            'first_booking.location.area_id' => ['nullable', 'integer', 'exists:areas,id'],
            'first_booking.location.zone_id' => ['nullable', 'integer', 'exists:zones,id'],
        ]);

        $customer = $request->user();

        $vehicle = $customer->vehicles()->find($data['vehicle_id']);
        if (! $vehicle) {
            throw ValidationException::withMessages(['vehicle_id' => ['Vehicle not found.']]);
        }

        $package = WashPackage::where('is_active', true)->find($data['wash_package_id']);
        if (! $package || $package->type !== 'multi' || (int) $package->visits_count < 1) {
            throw ValidationException::withMessages([
                'wash_package_id' => ['This is not a multi-visit plan.'],
            ]);
        }

        return $data['payment_method'] === 'wallet'
            ? $this->buyWithWallet($customer, $package, $vehicle, $data)
            : $this->buyWithCard($request, $customer, $package, $vehicle, $data);
    }

    /**
     * Resolve the first visit through the same factory an ordinary booking
     * uses, so a plan's first appointment cannot disagree with a normal one
     * about slot validity, add-on validity or pricing.
     *
     * Returns null when the caller isn't scheduling a visit yet.
     */
    private function resolveFirstVisit($customer, WashPackage $package, $vehicle, array $data): ?array
    {
        $first = $data['first_booking'] ?? null;
        if (! $first) {
            return null;
        }

        return $this->bookings->resolveBooking($customer, [
            'vehicle_id' => $vehicle->id,
            'wash_package_id' => $package->id,
            'add_on_ids' => $first['add_on_ids'] ?? [],
            'location' => $first['location'] ?? [],
        ]);
    }

    private function buyWithWallet($customer, WashPackage $package, $vehicle, array $data): JsonResponse
    {
        $price = (float) $package->price;
        $visit = $this->resolveFirstVisit($customer, $package, $vehicle, $data);
        $extras = $visit ? (float) $visit['addonsTotal'] : 0.0;

        // One balance check for the whole basket: the plan plus any add-ons on
        // the first visit. A half-bought plan is not a state worth having.
        if ((float) $customer->wallet_balance < $price + $extras) {
            throw ValidationException::withMessages([
                'payment_method' => ['Insufficient wallet balance.'],
            ]);
        }

        [$plan, $appointment] = DB::transaction(function () use ($customer, $package, $vehicle, $price, $visit, $extras, $data) {
            $plan = $customer->packages()->create([
                'wash_package_id' => $package->id,
                'vehicle_id' => $vehicle->id,
                'visits_total' => (int) $package->visits_count,
                'visits_used' => 0,
                'price_paid' => $price,
                'payment_method' => 'wallet',
                'payment_status' => 'pending',
                'status' => CustomerPackage::STATUS_PENDING,
            ]);

            $customer->walletTransactions()->create([
                'kind' => WalletTransaction::KIND_BOOKING,
                'amount' => -$price,
                'note' => "Plan #{$plan->id} — {$package->name}",
            ]);

            $plan->setRelation('washPackage', $package);
            $plan->activate();

            if (! $visit) {
                return [$plan, null];
            }

            $slot = $this->bookings->lockBookableSlot($data['first_booking']['time_slot_id'], $customer->preferred_language === 'ar');

            $attributes = $this->bookings->attributes(
                $visit, $slot, $data['first_booking'],
                status: Appointment::STATUS_CONFIRMED,
                paymentMethod: 'package',
                paymentStatus: 'paid',
            );

            // The visit covers the service; only add-ons carry a charge.
            $attributes['base_price'] = 0.0;
            $attributes['addons_total'] = $extras;
            $attributes['total_price'] = $extras;
            $attributes['customer_package_id'] = $plan->id;

            $appointment = $customer->appointments()->create($attributes);

            if ($extras > 0) {
                $tx = $customer->walletTransactions()->create([
                    'kind' => WalletTransaction::KIND_BOOKING,
                    'amount' => -$extras,
                    'note' => "Booking #{$appointment->id} — add-ons",
                ]);
                $appointment->update(['wallet_transaction_id' => $tx->id]);
            }

            $plan->increment('visits_used');
            $slot->increment('booked_count');

            return [$plan, $appointment];
        });

        // Outside the transaction: FCM network I/O has no business holding the
        // slot lock open.
        if ($appointment) {
            $this->notifications->customerBooked($appointment);
            $appointment->load(['washPackage', 'customerPackage', 'vehicle', 'timeSlot', 'area', 'zone']);
        }

        $plan->load(self::WITH);

        return response()->json([
            'data' => [
                'package' => new CustomerPackageResource($plan),
                'appointment' => $appointment ? new AppointmentResource($appointment) : null,
                'payment' => ['method' => 'wallet', 'status' => 'paid', 'payment_page_url' => null],
            ],
        ], 201);
    }

    private function buyWithCard(Request $request, $customer, WashPackage $package, $vehicle, array $data): JsonResponse
    {
        if (! $this->arb->isConfigured()) {
            throw ValidationException::withMessages([
                'payment_method' => ['Card payment is not available yet. Please pay with your wallet.'],
            ]);
        }

        $price = (float) $package->price;
        $visit = $this->resolveFirstVisit($customer, $package, $vehicle, $data);
        $extras = $visit ? (float) $visit['addonsTotal'] : 0.0;

        // Plan and first visit are created pending together and settled
        // together: one hosted page for the whole basket, so the customer never
        // pays twice or ends up with a plan and no booking.
        [$plan, $appointment, $payment] = DB::transaction(function () use ($customer, $package, $vehicle, $price, $extras, $visit, $data) {
            $plan = $customer->packages()->create([
                'wash_package_id' => $package->id,
                'vehicle_id' => $vehicle->id,
                'visits_total' => (int) $package->visits_count,
                'visits_used' => 0,
                'price_paid' => $price,
                'payment_method' => $data['payment_method'],
                'payment_status' => 'pending',
                'status' => CustomerPackage::STATUS_PENDING,
            ]);

            $appointment = null;
            if ($visit) {
                // Validated now so a bad slot fails before the customer pays,
                // but no seat and no visit are consumed until capture.
                $slot = $this->bookings->lockBookableSlot($data['first_booking']['time_slot_id'], $customer->preferred_language === 'ar');

                $attributes = $this->bookings->attributes(
                    $visit, $slot, $data['first_booking'],
                    status: Appointment::STATUS_PENDING,
                    paymentMethod: 'package',
                    paymentStatus: 'pending',
                );
                $attributes['base_price'] = 0.0;
                $attributes['addons_total'] = $extras;
                $attributes['total_price'] = $extras;
                $attributes['customer_package_id'] = $plan->id;

                $appointment = $customer->appointments()->create($attributes);
            }

            $payment = PaymentTransaction::create([
                'customer_id' => $customer->id,
                'appointment_id' => $appointment?->id,
                'customer_package_id' => $plan->id,
                'gateway' => 'arb',
                'purpose' => 'package_purchase',
                'action' => 'purchase',
                'status' => PaymentTransaction::STATUS_PENDING,
                'amount' => $price + $extras,
                'currency' => 'SAR',
                'track_id' => 'PK-'.$plan->id.'-'.Str::upper(Str::random(10)),
            ]);

            return [$plan, $appointment, $payment];
        });

        try {
            $token = $this->arb->createPurchaseToken([
                'amount' => $price + $extras,
                'track_id' => $payment->track_id,
                'response_url' => $this->callbackUrl('callback'),
                'error_url' => $this->callbackUrl('error'),
                'lang' => $customer->preferred_language,
                'customer_ip' => $request->ip(),
                'udf1' => 'package_purchase',
            ]);
        } catch (\Throwable $e) {
            // Compensate: nothing was consumed — no seat, no visit, no money.
            DB::transaction(function () use ($plan, $appointment, $payment, $e) {
                $payment->update([
                    'status' => PaymentTransaction::STATUS_FAILED,
                    'error_text' => mb_substr($e->getMessage(), 0, 250),
                ]);
                $appointment?->update([
                    'status' => Appointment::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ]);
                $plan->update([
                    'payment_status' => 'failed',
                    'status' => CustomerPackage::STATUS_CANCELLED,
                ]);
            });

            Log::warning('ARB plan token generation failed', [
                'plan' => $plan->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'message' => 'Could not start card payment. Please try again.',
            ], 502);
        }

        $payment->update(['payment_id' => $token['payment_id']]);
        $plan->load(self::WITH);
        $appointment?->load(['washPackage', 'customerPackage', 'vehicle', 'timeSlot', 'area', 'zone']);

        return response()->json([
            'data' => [
                'package' => new CustomerPackageResource($plan),
                'appointment' => $appointment ? new AppointmentResource($appointment) : null,
                'payment' => [
                    'method' => $data['payment_method'],
                    'status' => 'pending',
                    'payment_page_url' => $token['payment_url'],
                ],
            ],
        ], 201);
    }

    private function callbackUrl(string $type): string
    {
        $base = rtrim((string) config('services.arb.callback_base'), '/');

        return "{$base}/api/v1/payments/arb/{$type}";
    }
}
