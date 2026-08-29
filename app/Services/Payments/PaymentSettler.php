<?php

namespace App\Services\Payments;

use App\Models\Appointment;
use App\Models\CustomerPackage;
use App\Models\PaymentTransaction;
use App\Models\TimeSlot;
use App\Models\WalletTransaction;
use App\Services\Notifications\NotificationDispatcher;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Turns a verdict from the bank into its consequences: the transaction row, the
 * booking, the plan, the wallet and the customer's notification.
 *
 * Extracted from PaymentController so the reconciliation command settles the
 * same way the callback does. Two copies of this would eventually disagree, and
 * the disagreement would be about whether somebody's money bought them
 * anything.
 *
 * Idempotent by design: a transaction already captured is left alone, so the
 * redirect, the webhook and the reconciliation sweep can all arrive for the
 * same payment without double-crediting a wallet or spending a visit twice.
 */
class PaymentSettler
{
    public function __construct(private readonly NotificationDispatcher $notifications) {}

    /**
     * @param  array{payment_id:?string, track_id:?string, trans_id:?string, ref:?string,
     *               result:?string, amt:mixed, captured:bool, raw:mixed}  $parsed
     */
    public function apply(array $parsed): void
    {
        $payment = PaymentTransaction::query()
            ->when($parsed['payment_id'] ?? null, fn ($q) => $q->where('payment_id', $parsed['payment_id']))
            ->when(! ($parsed['payment_id'] ?? null) && ($parsed['track_id'] ?? null),
                fn ($q) => $q->where('track_id', $parsed['track_id']))
            ->latest('id')
            ->first();

        if (! $payment || $payment->status === PaymentTransaction::STATUS_CAPTURED) {
            return;
        }

        $appointment = $payment->appointment;

        if ($parsed['captured']) {
            $payment->update([
                'status' => PaymentTransaction::STATUS_CAPTURED,
                'trans_id' => $parsed['trans_id'] ?? null,
                'ref' => $parsed['ref'] ?? null,
                'result_code' => $parsed['result'] ?? null,
                'response_payload' => $parsed['raw'] ?? null,
            ]);

            // Order matters: a plan bought together with its first visit must
            // be activated BEFORE the booking is confirmed, because confirming
            // spends a visit and an inactive plan has none to give.
            if ($payment->purpose === 'package_purchase') {
                // Start the validity window now, not at purchase — a card plan
                // must not count down while the customer is on the hosted page.
                $plan = $payment->customerPackage;
                if ($plan && $plan->status === CustomerPackage::STATUS_PENDING) {
                    $plan->loadMissing('washPackage');
                    $plan->activate();
                }
            }

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
            'result_code' => $parsed['result'] ?? null,
            'response_payload' => $parsed['raw'] ?? null,
        ]);

        // A plan that was never paid for is dead — nothing to release, since
        // visits only become spendable on capture. Its first booking goes with
        // it: there is no plan left to draw a visit from.
        if ($payment->purpose === 'package_purchase') {
            $payment->customerPackage?->update([
                'payment_status' => 'failed',
                'status' => CustomerPackage::STATUS_CANCELLED,
            ]);
        }

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

            // A plan booking's add-ons have now been paid, so the visit is
            // finally spent — deliberately here and not at creation, so an
            // abandoned payment never costs the customer a visit.
            if ($appointment->isPackageCovered()) {
                $plan = CustomerPackage::lockForUpdate()->find($appointment->customer_package_id);

                if (! $plan || ! $plan->isUsable()) {
                    // The plan ran out or lapsed while they were paying. Refund
                    // the add-ons and cancel rather than booking a visit that
                    // does not exist.
                    $appointment->customer?->walletTransactions()->create([
                        'kind' => WalletTransaction::KIND_REFUND,
                        'amount' => (float) $payment->amount,
                        'note' => "Refund — booking #{$appointment->id}: plan no longer usable",
                    ]);
                    $appointment->update([
                        'status' => Appointment::STATUS_CANCELLED,
                        'payment_status' => 'refunded',
                        'cancelled_at' => now(),
                    ]);
                    Log::warning('Paid plan booking refunded — plan unusable at capture', [
                        'appointment' => $appointment->id,
                    ]);

                    return;
                }

                $plan->increment('visits_used');
            }

            $slot?->increment('booked_count');
            $appointment->update([
                'status' => Appointment::STATUS_CONFIRMED,
                'payment_status' => 'paid',
            ]);
        });

        if ($appointment->fresh()?->status === Appointment::STATUS_CONFIRMED) {
            // Inbox row + push, via the dispatcher so both channels stay in step.
            $this->notifications->customerBooked($appointment);
        }
    }
}
