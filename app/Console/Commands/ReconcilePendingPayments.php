<?php

namespace App\Console\Commands;

use App\Models\AppSetting;
use App\Models\PaymentTransaction;
use App\Services\ARB\ArbGateway;
use App\Services\Payments\PaymentSettler;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

/**
 * Asks the bank what really happened to payments we never heard back about.
 *
 * A charge is settled by the customer's redirect or the bank's webhook. Both
 * can be lost — the customer closes the app on the hosted page, the webhook
 * times out — and the booking then sits pending forever while the money has
 * actually left the customer's account. Nothing else would ever notice: the
 * row simply stays "pending" alongside the genuine abandonments.
 *
 * Neoleap offers no way to list transactions; inquiry takes one identifier at a
 * time (spec Q6), so this walks our own pending rows and asks about each.
 *
 * Settling goes through PaymentSettler, the same path the callback uses, which
 * is idempotent — a payment already captured is left alone, so this sweep can
 * race the webhook without double-crediting anything.
 */
class ReconcilePendingPayments extends Command
{
    protected $signature = 'payments:reconcile
                            {--minutes= : Only rows older than this many minutes (default: the booking grace window)}
                            {--limit=100 : Most rows to check in one run}
                            {--dry-run : Report what would change without changing it}';

    protected $description = 'Ask the gateway about pending payments and settle any it reports as captured';

    public function handle(ArbGateway $arb, PaymentSettler $settler): int
    {
        if (! $arb->isConfigured()) {
            $this->warn('Gateway is not configured — nothing to reconcile.');

            return self::SUCCESS;
        }

        // Younger than the grace window the customer may still be on the bank's
        // page, and the redirect is about to settle it properly.
        $minutes = (int) ($this->option('minutes')
            ?? AppSetting::get('booking.pending_grace_minutes', '30'));

        $dryRun = (bool) $this->option('dry-run');

        $pending = PaymentTransaction::query()
            ->where('status', PaymentTransaction::STATUS_PENDING)
            ->where('created_at', '<', now()->subMinutes(max(1, $minutes)))
            // Nothing to ask about without an identifier the bank knows.
            ->where(fn ($q) => $q->whereNotNull('payment_id')->orWhereNotNull('track_id'))
            ->orderBy('id')
            ->limit(max(1, (int) $this->option('limit')))
            ->get();

        if ($pending->isEmpty()) {
            $this->info('No pending payments old enough to reconcile.');

            return self::SUCCESS;
        }

        $captured = $failed = $unknown = $errors = 0;

        foreach ($pending as $payment) {
            try {
                $result = $arb->inquire([
                    'track_id' => $payment->track_id,
                    'payment_id' => $payment->payment_id,
                    'trans_id' => $payment->trans_id,
                    'amount' => (float) $payment->amount,
                ]);
            } catch (\Throwable $e) {
                // One unreachable lookup must not stop the sweep; the row stays
                // pending and the next run tries again.
                $errors++;
                Log::warning('[reconcile] inquiry failed', [
                    'payment' => $payment->id,
                    'error' => $e->getMessage(),
                ]);

                continue;
            }

            if (! ($result['found'] ?? false)) {
                // The bank has no record: the customer never entered a card.
                $unknown++;

                continue;
            }

            if ($result['captured'] ?? false) {
                $captured++;
                $this->warn("  #{$payment->id} was CAPTURED at the bank but still pending here".
                    ($dryRun ? ' (dry run)' : ' — settling'));

                Log::warning('[reconcile] captured payment had not been settled', [
                    'payment' => $payment->id,
                    'appointment' => $payment->appointment_id,
                    'amount' => (float) $payment->amount,
                ]);
            } else {
                $failed++;
                $this->line("  #{$payment->id} declined at the bank".($dryRun ? ' (dry run)' : ' — closing'));
            }

            if (! $dryRun) {
                $settler->apply($result);
            }
        }

        $this->info(sprintf(
            'Checked %d: %d captured, %d declined, %d unknown to the bank, %d lookup errors.%s',
            $pending->count(), $captured, $failed, $unknown, $errors, $dryRun ? ' (dry run)' : ''
        ));

        return self::SUCCESS;
    }
}
