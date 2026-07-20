<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\CustomerNotification;
use App\Models\PaymentTransaction;
use App\Models\TimeSlot;
use App\Models\WalletTransaction;
use App\Services\ARB\ArbGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

/**
 * Public ARB/Neoleap callbacks. These are hit by the bank, not the app, so
 * they carry no customer token. The app's WebView watches for navigation to
 * `/payments/arb/done` to know the flow finished, then re-fetches the booking.
 */
class PaymentController extends Controller
{
    public function __construct(private readonly ArbGateway $arb)
    {
    }

    /** responseURL — final redirect for a completed (success/declined) transaction. */
    public function callback(Request $request): Response
    {
        $status = $this->handleRedirect($request);

        return $this->doneRedirect($status);
    }

    /** errorURL — gateway/processing error before a result was reached. */
    public function error(Request $request): Response
    {
        $this->handleRedirect($request, forceFailure: true);

        return $this->doneRedirect('failed');
    }

    /** Webhook — server-to-server notification (reliable channel). Idempotent. */
    public function webhook(Request $request): JsonResponse
    {
        $trandata = $request->input('trandata')
            ?? data_get($request->input('payLoad'), 'trandata');

        try {
            if ($trandata) {
                $this->applyResult($this->arb->parseFinalResponse($trandata));
            } else {
                // Some webhook payloads are plain JSON (payLoad + result).
                $this->applyFromWebhookPayload($request->all());
            }
        } catch (\Throwable $e) {
            Log::warning('ARB webhook processing failed', ['error' => $e->getMessage()]);

            return response()->json(['received' => true]); // ack to avoid retries storm
        }

        return response()->json(['received' => true]);
    }

    /** Terminal page the in-app WebView detects to close and refresh. */
    public function done(Request $request): Response
    {
        $status = $request->query('status', 'unknown');

        return response(
            "<!doctype html><html><head><meta charset=\"utf-8\"><title>Payment {$status}</title></head>".
            "<body>Payment {$status}. You can return to the app.</body></html>"
        )->header('Content-Type', 'text/html');
    }

    // --- internals -------------------------------------------------------

    private function handleRedirect(Request $request, bool $forceFailure = false): string
    {
        $trandata = $request->input('trandata');

        try {
            if (! $trandata) {
                return 'failed';
            }
            $parsed = $this->arb->parseFinalResponse($trandata);
            if ($forceFailure) {
                $parsed['captured'] = false;
            }
            $this->applyResult($parsed);

            return $parsed['captured'] ? 'success' : 'failed';
        } catch (\Throwable $e) {
            Log::warning('ARB callback processing failed', ['error' => $e->getMessage()]);

            return 'failed';
        }
    }

    /**
     * Apply a decrypted final response to the matching payment + appointment.
     * Idempotent: a transaction already captured is left untouched.
     */
    private function applyResult(array $parsed): void
    {
        $payment = PaymentTransaction::query()
            ->when($parsed['payment_id'], fn ($q) => $q->where('payment_id', $parsed['payment_id']))
            ->when(! $parsed['payment_id'] && $parsed['track_id'], fn ($q) => $q->where('track_id', $parsed['track_id']))
            ->latest('id')
            ->first();

        if (! $payment || $payment->status === PaymentTransaction::STATUS_CAPTURED) {
            return;
        }

        $appointment = $payment->appointment;

        if ($parsed['captured']) {
            $payment->update([
                'status' => PaymentTransaction::STATUS_CAPTURED,
                'trans_id' => $parsed['trans_id'],
                'ref' => $parsed['ref'],
                'result_code' => $parsed['result'],
                'response_payload' => $parsed['raw'],
            ]);

            if ($appointment && $appointment->status === Appointment::STATUS_PENDING) {
                $this->confirmPaidAppointment($appointment, $payment);
            } elseif ($payment->purpose === 'wallet_topup') {
                // Credit the wallet (the create() hook increments wallet_balance).
                $payment->customer?->walletTransactions()->create([
                    'kind' => WalletTransaction::KIND_TOP_UP,
                    'amount' => (float) $payment->amount,
                    'note' => 'Wallet top-up (card)',
                ]);
            }

            return;
        }

        // Declined / failed → void the pending hold and free the slot.
        $payment->update([
            'status' => PaymentTransaction::STATUS_FAILED,
            'result_code' => $parsed['result'],
            'response_payload' => $parsed['raw'],
        ]);

        // Declined / failed → cancel the pending booking. No slot to free: a seat
        // is only consumed on capture, never held for an unpaid booking.
        if ($appointment && $appointment->status === Appointment::STATUS_PENDING) {
            $appointment->update([
                'status' => Appointment::STATUS_CANCELLED,
                'cancelled_at' => now(),
            ]);
        }
    }

    /**
     * Payment captured → consume the seat now (never before). Re-checks capacity
     * under a row lock; if the slot filled up while the customer was paying, the
     * charge is refunded to their wallet and the booking is cancelled.
     */
    private function confirmPaidAppointment(Appointment $appointment, PaymentTransaction $payment): void
    {
        DB::transaction(function () use ($appointment, $payment) {
            $slot = $appointment->time_slot_id
                ? TimeSlot::lockForUpdate()->find($appointment->time_slot_id)
                : null;

            if ($slot && $slot->booked_count >= $slot->capacity) {
                // Race: last seat taken by another paid booking. Refund + cancel.
                $appointment->customer?->walletTransactions()->create([
                    'kind' => WalletTransaction::KIND_REFUND,
                    'amount' => (float) $payment->amount,
                    'note' => "Refund — booking #{$appointment->id}: time slot no longer available",
                ]);
                $appointment->update([
                    'status' => Appointment::STATUS_CANCELLED,
                    'payment_status' => 'refunded',
                    'cancelled_at' => now(),
                ]);
                Log::warning('Paid booking refunded — slot full at capture', [
                    'appointment' => $appointment->id,
                ]);

                return;
            }

            $slot?->increment('booked_count');
            $appointment->update([
                'status' => Appointment::STATUS_CONFIRMED,
                'payment_status' => 'paid',
            ]);
        });

        if ($appointment->fresh()?->status === Appointment::STATUS_CONFIRMED) {
            $this->notifyBooked($appointment);
        }
    }

    private function applyFromWebhookPayload(array $payload): void
    {
        $row = data_get($payload, 'payLoad', $payload);
        $result = data_get($payload, 'result.result') ?? data_get($payload, 'result');

        $this->applyResult([
            'payment_id' => $row['paymentId'] ?? null,
            'track_id' => $row['trackId'] ?? null,
            'trans_id' => $row['transId'] ?? null,
            'ref' => $row['ref'] ?? null,
            'result' => is_string($result) ? $result : null,
            'amt' => $row['amt'] ?? null,
            'captured' => in_array($result, ['CAPTURED', 'APPROVED'], true),
            'raw' => $payload,
        ]);
    }

    private function notifyBooked(Appointment $appointment): void
    {
        $when = $appointment->scheduled_at?->format('Y-m-d H:i');

        $appointment->customer?->customerNotifications()->create([
            'kind' => CustomerNotification::KIND_BOOKING,
            'title' => 'Booking confirmed',
            'title_ar' => 'تم تأكيد الحجز',
            'body' => "{$appointment->service_name} on {$when}",
            'body_ar' => "{$appointment->service_name} بتاريخ {$when}",
            'data' => ['appointment_id' => $appointment->id],
        ]);
    }

    private function doneRedirect(string $status): Response
    {
        $base = rtrim((string) config('services.arb.callback_base'), '/');

        return redirect("{$base}/api/v1/payments/arb/done?status={$status}");
    }
}
