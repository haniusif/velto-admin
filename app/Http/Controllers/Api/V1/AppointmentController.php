<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\AppointmentResource;
use App\Models\Appointment;
use App\Models\AppointmentReview;
use App\Models\CustomerPackage;
use App\Models\PaymentTransaction;
use App\Models\TimeSlot;
use App\Models\Vehicle;
use App\Models\WalletTransaction;
use App\Models\WashPackage;
use App\Models\WorkerLocation;
use App\Services\ARB\ArbGateway;
use App\Services\Booking\BookingFactory;
use App\Services\Notifications\NotificationDispatcher;
use App\Support\BookingTime;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    private const WITH = ['washPackage', 'customerPackage', 'review', 'vehicle', 'timeSlot', 'area', 'zone'];

    public function __construct(
        private readonly ArbGateway $arb,
        private readonly NotificationDispatcher $notifications,
        private readonly BookingFactory $bookings,
    ) {}

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
            'payment_method' => ['required', 'string', 'in:wallet,card,apple_pay,package'],
            'customer_package_id' => ['nullable', 'integer'],
            'addons_payment_method' => ['nullable', 'string', 'in:wallet,card,apple_pay'],
            'promo_code' => ['nullable', 'string', 'max:40'],
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

        if ($data['payment_method'] === 'package') {
            return $this->createPackageBooking($request, $customer, $booking, $data);
        }

        if ($data['payment_method'] === 'wallet') {
            return $this->createWalletBooking($customer, $booking, $data);
        }

        return $this->createCardBooking($request, $customer, $booking, $data);
    }

    /** POST /api/v1/me/appointments/{appointment}/cancel */
    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwn($request, $appointment);

        if (! $appointment->canCancel()) {
            // A machine-readable code so the app can show this in the user's
            // language — `message` stays as an English fallback for anything
            // that doesn't recognise the code.
            return response()->json([
                'message' => sprintf(
                    'Bookings can only be cancelled more than %d hours before the appointment. Please contact support.',
                    Appointment::CANCELLATION_CUTOFF_HOURS,
                ),
                'code' => 'cancellation_window_closed',
                'cutoff_hours' => Appointment::CANCELLATION_CUTOFF_HOURS,
            ], 422);
        }

        // A plan booking cost a visit plus, if it had add-ons, money. The visit
        // is refunded in kind; the money follows the usual rules — and for a
        // plan booking the method isn't in payment_method, so a captured
        // transaction is what says whether it was a card.
        $isPackageCovered = $appointment->isPackageCovered();

        $isCardPaid = $appointment->payment_status === 'paid'
            && ($isPackageCovered
                ? (float) $appointment->total_price > 0 && $this->hasCapturedCard($appointment)
                : $appointment->payment_method !== 'wallet');

        // Captured before the update so the notification still reaches the
        // worker even if the assignment is cleared later.
        $assignedWorkerId = $appointment->worker_id;

        DB::transaction(function () use ($appointment, $isCardPaid, $isPackageCovered) {
            $appointment->update([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);

            $this->releaseSlot($appointment->time_slot_id);
            $appointment->releasePromoCode();

            // Give the visit back. Guarded so a double-cancel cannot mint
            // visits, and the plan's own expiry still applies to the returned
            // one — a refund does not extend the window. A booking still
            // awaiting its add-ons payment never spent a visit, so there is
            // nothing to return.
            if ($isPackageCovered && $appointment->payment_status === 'paid') {
                $plan = CustomerPackage::lockForUpdate()->find($appointment->customer_package_id);
                if ($plan && $plan->visits_used > 0) {
                    $plan->decrement('visits_used');
                }
            }

            // Wallet refund is an internal ledger entry; card refund is external
            // (below). Only add-ons carry a charge on a plan booking, so a
            // zero-total one has nothing further to refund.
            if ($appointment->payment_status === 'paid'
                && ! $isCardPaid
                && (float) $appointment->total_price > 0) {
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

        // Outside the transaction: a push failure must not roll back a
        // cancellation the customer has already been told succeeded.
        $this->notifications->workerJobCancelled($appointment, $assignedWorkerId);

        $appointment->load(self::WITH);

        return response()->json(['data' => new AppointmentResource($appointment)]);
    }

    /**
     * GET /api/v1/me/appointments/{appointment}/tracking
     *
     * Where the specialist is, but only while they are actually coming: on the
     * way or arrived. Before that there is nothing to show, and afterwards
     * they are at the car, so a live dot serves no purpose and only prolongs
     * how long a staff position is exposed.
     */
    public function tracking(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwn($request, $appointment);

        $trackable = in_array($appointment->status, [
            Appointment::STATUS_ON_THE_WAY,
            Appointment::STATUS_ARRIVED,
        ], true);

        $location = $trackable && $appointment->worker_id
            ? WorkerLocation::where('worker_id', $appointment->worker_id)->first()
            : null;

        // A stale dot is worse than none: it asserts a position the specialist
        // may have left minutes ago.
        $live = $location !== null && $location->isFresh();

        return response()->json([
            'data' => [
                'trackable' => $trackable,
                'status' => $appointment->status,
                'worker' => $appointment->worker_id ? [
                    'name' => $appointment->worker?->name,
                    'phone' => $appointment->worker?->phone,
                ] : null,
                'worker_location' => $live ? [
                    'lat' => (float) $location->lat,
                    'lng' => (float) $location->lng,
                    'heading' => $location->heading === null ? null : (float) $location->heading,
                    'updated_at' => $location->updated_at?->toIso8601String(),
                ] : null,
                'destination' => [
                    'lat' => $appointment->latitude,
                    'lng' => $appointment->longitude,
                    'label' => $appointment->address_label,
                ],
            ],
        ]);
    }

    /**
     * POST /api/v1/me/appointments/{appointment}/review
     *
     * Rate a finished job. One review per appointment — the unique index is
     * the real guard; this check just turns a database error into a message.
     */
    public function review(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwn($request, $appointment);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:'.AppointmentReview::MIN_RATING, 'max:'.AppointmentReview::MAX_RATING],
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        if ($appointment->status !== Appointment::STATUS_COMPLETED) {
            return response()->json([
                'message' => 'Only completed bookings can be rated.',
                'code' => 'booking_not_completed',
            ], 422);
        }

        if ($appointment->review !== null) {
            return response()->json([
                'message' => 'This booking has already been rated.',
                'code' => 'already_reviewed',
            ], 422);
        }

        $review = DB::transaction(function () use ($appointment, $data) {
            $review = AppointmentReview::create([
                'appointment_id' => $appointment->id,
                'customer_id' => $appointment->customer_id,
                'worker_id' => $appointment->worker_id,
                'rating' => $data['rating'],
                'comment' => $data['comment'] ?? null,
            ]);

            // Keep the worker's headline rating in step with the review that
            // just landed, inside the same transaction.
            AppointmentReview::refreshWorkerRating($appointment->worker_id);

            return $review;
        });

        $appointment->setRelation('review', $review);
        $appointment->load(self::WITH);

        return response()->json(['data' => new AppointmentResource($appointment)]);
    }

    /**
     * POST /api/v1/me/appointments/{appointment}/pay
     *
     * Re-issue a card payment token for a pending booking whose payment was
     * never completed. The slot is already held, so we only mint a fresh ARB
     * purchase token and hand back a new hosted-page URL.
     */
    public function pay(Request $request, Appointment $appointment): JsonResponse
    {
        $this->authorizeOwn($request, $appointment);

        if (! $appointment->canPay()) {
            throw ValidationException::withMessages([
                'appointment' => ['This booking is not awaiting payment.'],
            ]);
        }

        if (! $this->arb->isConfigured()) {
            throw ValidationException::withMessages([
                'payment_method' => ['Card payment is not available yet. Please pay with your wallet.'],
            ]);
        }

        $customer = $request->user();

        $payment = PaymentTransaction::create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'gateway' => 'arb',
            'action' => 'purchase',
            'status' => PaymentTransaction::STATUS_PENDING,
            'amount' => (float) $appointment->total_price,
            'currency' => 'SAR',
            'track_id' => $this->bookingTrackId($appointment),
        ]);

        try {
            $token = $this->arb->createPurchaseToken([
                'amount' => (float) $appointment->total_price,
                'track_id' => $payment->track_id,
                'response_url' => $this->callbackUrl('callback'),
                'error_url' => $this->callbackUrl('error'),
                'lang' => $customer->preferred_language,
                'customer_ip' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            $payment->update([
                'status' => PaymentTransaction::STATUS_FAILED,
                'error_text' => mb_substr($e->getMessage(), 0, 250),
            ]);
            Log::warning('ARB token re-issue failed', ['appointment' => $appointment->id, 'error' => $e->getMessage()]);

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
                    'method' => $appointment->payment_method,
                    'status' => 'pending',
                    'payment_page_url' => $token['payment_url'],
                ],
            ],
        ]);
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

            // Stored naive, compared in the business timezone — see BookingTime.
            $scheduledAt = Carbon::parse($newSlot->date->toDateString().' '.$newSlot->start_time);

            if (! BookingTime::isBookable($newSlot->date->toDateString(), $newSlot->start_time)) {
                throw ValidationException::withMessages([
                    'time_slot_id' => [BookingTime::leadTimeMessage($request->user()?->preferred_language === 'ar')],
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
     * @return array{vehicle:Vehicle, package:WashPackage, addOns:Collection, base:float, addonsTotal:float, total:float, location:array}
     */
    private function resolveBooking($customer, array $data): array
    {
        return $this->bookings->resolveBooking($customer, $data);
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

            return $appointment;
        });

        // Outside the transaction: notifying now sends an FCM request per device,
        // and network I/O has no business holding the slot lock open.
        $this->notifyBooked($appointment);

        $appointment->load(self::WITH);

        return response()->json([
            'data' => [
                'appointment' => new AppointmentResource($appointment),
                'payment' => ['method' => 'wallet', 'status' => 'paid', 'payment_page_url' => null],
            ],
        ], 201);
    }

    /**
     * Book against a prepaid plan. The visit pays for the service itself; any
     * add-ons are extra and still have to be paid for, by wallet inline or by
     * card through the hosted page.
     *
     * The plan row is locked for the whole check-and-decrement so two bookings
     * racing for the last visit cannot both take it — the same reason the slot
     * is locked.
     */
    private function createPackageBooking(Request $request, $customer, array $b, array $data): JsonResponse
    {
        $planId = $data['customer_package_id'] ?? null;
        if (! $planId) {
            throw ValidationException::withMessages([
                'customer_package_id' => ['Choose which plan to use.'],
            ]);
        }

        $extras = (float) $b['addonsTotal'];
        $extrasMethod = $data['addons_payment_method'] ?? 'wallet';
        $payByCard = $extras > 0 && $extrasMethod !== 'wallet';

        if ($payByCard && ! $this->arb->isConfigured()) {
            throw ValidationException::withMessages([
                'addons_payment_method' => ['Card payment is not available yet. Please pay with your wallet.'],
            ]);
        }

        if ($extras > 0 && ! $payByCard && (float) $customer->wallet_balance < $extras) {
            throw ValidationException::withMessages([
                'addons_payment_method' => ['Insufficient wallet balance for the add-ons.'],
            ]);
        }

        [$appointment, $payment] = DB::transaction(function () use ($customer, $b, $data, $planId, $extras, $payByCard) {
            $plan = $this->lockUsablePlan($customer, $planId, $b);

            $slot = $this->lockBookableSlot($data['time_slot_id']);

            $attributes = $this->attributes(
                $b, $slot, $data,
                // Unpaid extras leave the booking pending, exactly like a card
                // booking: the seat and the visit are only taken on capture.
                status: $payByCard ? Appointment::STATUS_PENDING : Appointment::STATUS_CONFIRMED,
                paymentMethod: 'package',
                paymentStatus: $payByCard ? 'pending' : 'paid',
            );

            // The visit covers the service; only the add-ons carry a charge.
            $attributes['base_price'] = 0.0;
            $attributes['addons_total'] = $extras;
            $attributes['total_price'] = $extras;
            $attributes['customer_package_id'] = $plan->id;

            $appointment = $customer->appointments()->create($attributes);

            if ($payByCard) {
                $payment = PaymentTransaction::create([
                    'customer_id' => $customer->id,
                    'appointment_id' => $appointment->id,
                    'gateway' => 'arb',
                    'action' => 'purchase',
                    'status' => PaymentTransaction::STATUS_PENDING,
                    'amount' => $extras,
                    'currency' => 'SAR',
                    'track_id' => $this->bookingTrackId($appointment),
                ]);

                return [$appointment, $payment];
            }

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

            return [$appointment, null];
        });

        if ($payment !== null) {
            return $this->startExtrasPayment($request, $customer, $appointment, $payment, $extras, $extrasMethod);
        }

        $this->notifyBooked($appointment);
        $appointment->load(self::WITH);

        return response()->json([
            'data' => [
                'appointment' => new AppointmentResource($appointment),
                'payment' => [
                    'method' => $extras > 0 ? 'wallet' : 'package',
                    'status' => 'paid',
                    'payment_page_url' => null,
                ],
            ],
        ], 201);
    }

    /**
     * Load the plan under a row lock and check it can pay for this booking.
     * Shared by the create path so every refusal reads the same.
     */
    private function lockUsablePlan($customer, int $planId, array $b): CustomerPackage
    {
        /** @var CustomerPackage|null $plan */
        $plan = $customer->packages()->lockForUpdate()->find($planId);

        if (! $plan) {
            throw ValidationException::withMessages([
                'customer_package_id' => ['Plan not found.'],
            ]);
        }

        if ($plan->wash_package_id !== $b['package']->id) {
            throw ValidationException::withMessages([
                'customer_package_id' => ['This plan does not cover the selected service.'],
            ]);
        }

        if ($plan->vehicle_id !== $b['vehicle']->id) {
            throw ValidationException::withMessages([
                'customer_package_id' => ['This plan is locked to a different vehicle.'],
            ]);
        }

        if (! $plan->isUsable()) {
            throw ValidationException::withMessages([
                'customer_package_id' => [$plan->visitsRemaining() < 1
                    ? 'This plan has no visits left.'
                    : 'This plan is not active.'],
            ]);
        }

        return $plan;
    }

    /** Mint the hosted-page token for a plan booking's unpaid add-ons. */
    private function startExtrasPayment(
        Request $request,
        $customer,
        Appointment $appointment,
        PaymentTransaction $payment,
        float $extras,
        string $method,
    ): JsonResponse {
        try {
            $token = $this->arb->createPurchaseToken([
                'amount' => $extras,
                'track_id' => $payment->track_id,
                'response_url' => $this->callbackUrl('callback'),
                'error_url' => $this->callbackUrl('error'),
                'lang' => $customer->preferred_language,
                'customer_ip' => $request->ip(),
            ]);
        } catch (\Throwable $e) {
            // Compensate: nothing was consumed — no seat, no visit.
            DB::transaction(function () use ($appointment, $payment, $e) {
                $appointment->update([
                    'status' => Appointment::STATUS_CANCELLED,
                    'cancelled_at' => now(),
                ]);
                $payment->update([
                    'status' => PaymentTransaction::STATUS_FAILED,
                    'error_text' => mb_substr($e->getMessage(), 0, 250),
                ]);
            });
            Log::warning('ARB add-ons token generation failed', [
                'appointment' => $appointment->id,
                'error' => $e->getMessage(),
            ]);

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
                    'method' => $method,
                    'status' => 'pending',
                    'payment_page_url' => $token['payment_url'],
                ],
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

        // Create the booking as pending BEFORE redirecting to pay. The slot is
        // NOT reserved here — a booking only consumes a seat once payment is
        // captured (see PaymentController::applyResult). We still validate the
        // slot is currently bookable so the user gets immediate feedback.
        [$appointment, $payment] = DB::transaction(function () use ($customer, $b, $data) {
            $slot = $this->lockBookableSlot($data['time_slot_id']);

            $appointment = $customer->appointments()->create($this->attributes(
                $b, $slot, $data,
                status: Appointment::STATUS_PENDING,
                paymentMethod: $data['payment_method'],
                paymentStatus: 'pending',
            ));

            $payment = PaymentTransaction::create([
                'customer_id' => $customer->id,
                'appointment_id' => $appointment->id,
                'gateway' => 'arb',
                'action' => 'purchase',
                'status' => PaymentTransaction::STATUS_PENDING,
                'amount' => $b['total'],
                'currency' => 'SAR',
                'track_id' => $this->bookingTrackId($appointment),
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
            // Compensate: void the pending booking/payment. No slot to release —
            // the seat is only taken on payment capture.
            DB::transaction(function () use ($appointment, $payment, $e) {
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
        return $this->bookings->attributes($b, $slot, $data, $status, $paymentMethod, $paymentStatus);
    }

    /** Lock a bookable slot row and validate it; throws on unavailable/full/past. */
    /**
     * POST /me/appointments/{appointment}/verify-payment
     * Ask Neoleap directly for the transaction's real state (never trust the
     * redirect `status`), then settle the booking accordingly. Idempotent and
     * safe to call repeatedly / concurrently with the bank callback.
     */
    public function verifyPayment(Request $request, Appointment $appointment): JsonResponse
    {
        abort_unless($appointment->customer_id === $request->user()?->id, 404);

        if ($appointment->status === Appointment::STATUS_PENDING && $this->arb->isConfigured()) {
            $payment = PaymentTransaction::where('appointment_id', $appointment->id)
                ->where('gateway', 'arb')
                ->latest('id')
                ->first();

            if ($payment && $payment->status !== PaymentTransaction::STATUS_CAPTURED) {
                try {
                    $inq = $this->arb->inquire([
                        'track_id' => $payment->track_id,
                        'payment_id' => $payment->payment_id,
                        'trans_id' => $payment->trans_id,
                        'amount' => $payment->amount,
                    ]);

                    if (($inq['found'] ?? false) && $inq['captured']) {
                        $this->settlePaidBooking($appointment, $payment, $inq);
                    } elseif (($inq['found'] ?? false) && ! $inq['captured']) {
                        $this->settleFailedBooking($appointment, $payment, $inq);
                    }
                    // Not found / still processing → leave pending; the cron and
                    // the bank callback remain the fallback.
                } catch (\Throwable $e) {
                    Log::warning('ARB inquiry failed', [
                        'appointment' => $appointment->id,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        }

        return response()->json([
            'data' => new AppointmentResource($appointment->fresh()->load(self::WITH)),
        ]);
    }

    /** Confirm a booking whose payment Neoleap reports as captured (consumes the seat now). */
    private function settlePaidBooking(Appointment $appointment, PaymentTransaction $payment, array $inq): void
    {
        DB::transaction(function () use ($appointment, $payment, $inq) {
            $fresh = Appointment::lockForUpdate()->find($appointment->id);
            if (! $fresh || $fresh->status !== Appointment::STATUS_PENDING) {
                return; // already settled by the callback — stay idempotent
            }

            $payment->update([
                'status' => PaymentTransaction::STATUS_CAPTURED,
                'trans_id' => $inq['trans_id'] ?? $payment->trans_id,
                'ref' => $inq['ref'] ?? $payment->ref,
                'result_code' => $inq['result'] ?? null,
            ]);

            $slot = $fresh->time_slot_id
                ? TimeSlot::lockForUpdate()->find($fresh->time_slot_id)
                : null;

            if ($slot && $slot->booked_count >= $slot->capacity) {
                $fresh->customer?->walletTransactions()->create([
                    'kind' => WalletTransaction::KIND_REFUND,
                    'amount' => (float) $payment->amount,
                    'note' => "Refund — booking #{$fresh->id}: time slot no longer available",
                ]);
                $fresh->update([
                    'status' => Appointment::STATUS_CANCELLED,
                    'payment_status' => 'refunded',
                    'cancelled_at' => now(),
                ]);

                return;
            }

            $slot?->increment('booked_count');
            $fresh->update([
                'status' => Appointment::STATUS_CONFIRMED,
                'payment_status' => 'paid',
            ]);
        });

        $settled = $appointment->fresh();
        if ($settled?->status === Appointment::STATUS_CONFIRMED) {
            $this->notifyBooked($settled);
        }
    }

    /** Cancel a booking whose payment Neoleap reports as not captured. */
    private function settleFailedBooking(Appointment $appointment, PaymentTransaction $payment, array $inq): void
    {
        DB::transaction(function () use ($appointment, $payment, $inq) {
            $fresh = Appointment::lockForUpdate()->find($appointment->id);
            if (! $fresh || $fresh->status !== Appointment::STATUS_PENDING) {
                return;
            }
            $payment->update([
                'status' => PaymentTransaction::STATUS_FAILED,
                'result_code' => $inq['result'] ?? null,
            ]);
            $fresh->update([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);
        });
    }

    private function lockBookableSlot(int $slotId): TimeSlot
    {
        return $this->bookings->lockBookableSlot($slotId, request()->user()?->preferred_language === 'ar');
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

    /** Whether a captured card payment exists for this booking. */
    private function hasCapturedCard(Appointment $appointment): bool
    {
        return PaymentTransaction::where('appointment_id', $appointment->id)
            ->where('status', PaymentTransaction::STATUS_CAPTURED)
            ->exists();
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

    /**
     * A merchant track id that is unique across the gateway's whole history.
     * The sequential appointment id collides in long-lived environments (the
     * gateway rejects a reused track id), so append a random suffix — matching
     * the wallet top-up scheme. The callback matches the payment by this stored
     * value, so the format is free.
     */
    private function bookingTrackId(Appointment $appointment): string
    {
        return 'BK-'.$appointment->id.'-'.Str::upper(Str::random(10));
    }

    private function callbackUrl(string $type): string
    {
        $base = rtrim((string) config('services.arb.callback_base'), '/');

        return "{$base}/api/v1/payments/arb/{$type}";
    }

    /** Inbox row + push, via the dispatcher so both channels stay in step. */
    private function notifyBooked(Appointment $appointment): void
    {
        $this->notifications->customerBooked($appointment);
    }

    private function authorizeOwn(Request $request, Appointment $appointment): void
    {
        abort_unless($appointment->customer_id === $request->user()?->id, 404);
    }
}
