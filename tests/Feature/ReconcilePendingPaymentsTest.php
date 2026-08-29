<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\PaymentTransaction;
use App\Models\TimeSlot;
use App\Services\ARB\ArbGateway;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * A charge is normally settled by the customer's redirect or the bank's
 * webhook. Both can be lost, and when they are the money has left the
 * customer's account while the booking sits pending — indistinguishable from
 * someone who simply walked away from the payment page.
 *
 * Neoleap cannot list transactions (spec Q6: inquiry is by identifier only), so
 * reconciliation means asking about our own pending rows one at a time.
 */
class ReconcilePendingPaymentsTest extends TestCase
{
    use RefreshDatabase;

    private function customer(): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create([
            'name' => "Customer {$n}",
            'phone' => '+96650000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
            'wallet_balance' => 0,
        ]);
    }

    private function pendingCardBooking(string $age = '-2 hours'): PaymentTransaction
    {
        $customer = $this->customer();

        $slot = TimeSlot::create([
            'date' => now()->addDay()->toDateString(),
            'start_time' => '16:00:00',
            'end_time' => '17:00:00',
            'capacity' => 3,
            'booked_count' => 0,
            'is_active' => true,
        ]);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'time_slot_id' => $slot->id,
            'status' => Appointment::STATUS_PENDING,
            'scheduled_at' => now()->addDay()->setTime(16, 0)->toDateTimeString(),
            'service_name' => 'Express exterior wash',
            'base_price' => 35,
            'addons_total' => 0,
            'discount_total' => 0,
            'total_price' => 35,
            'payment_method' => 'card',
            'payment_status' => 'pending',
        ]);

        $payment = PaymentTransaction::create([
            'customer_id' => $customer->id,
            'appointment_id' => $appointment->id,
            'gateway' => 'arb',
            'action' => 'purchase',
            'purpose' => 'booking',
            'status' => PaymentTransaction::STATUS_PENDING,
            'amount' => 35,
            'currency' => 'SAR',
            'track_id' => 'BK-'.uniqid(),
            'payment_id' => 'PID-'.uniqid(),
        ]);

        $payment->forceFill(['created_at' => now()->modify($age)])->save();

        return $payment->refresh();
    }

    /** Stands in for the bank, so no test ever reaches the real gateway. */
    private function gatewayReturning(array $result): void
    {
        $this->mock(ArbGateway::class, function ($mock) use ($result) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('inquire')->andReturn($result);
        });
    }

    private function captured(PaymentTransaction $p): array
    {
        return [
            'found' => true, 'captured' => true, 'result' => 'CAPTURED',
            'trans_id' => 'T1', 'ref' => 'R1',
            'payment_id' => $p->payment_id, 'track_id' => $p->track_id,
            'amt' => (string) $p->amount, 'raw' => ['result' => 'CAPTURED'],
        ];
    }

    public function test_a_payment_the_bank_captured_is_settled_and_the_booking_confirmed(): void
    {
        // The case this exists for: the customer paid, the callback was lost,
        // and nothing else would ever have noticed.
        $payment = $this->pendingCardBooking();
        $this->gatewayReturning($this->captured($payment));

        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame(PaymentTransaction::STATUS_CAPTURED, $payment->fresh()->status);
        $this->assertSame(Appointment::STATUS_CONFIRMED, $payment->appointment->fresh()->status);
        $this->assertSame('paid', $payment->appointment->fresh()->payment_status);
        // The seat is only taken on capture, so it must be taken now.
        $this->assertSame(1, $payment->appointment->timeSlot->fresh()->booked_count);
    }

    public function test_a_declined_payment_closes_its_booking(): void
    {
        $payment = $this->pendingCardBooking();
        $this->gatewayReturning([
            'found' => true, 'captured' => false, 'result' => 'NOT CAPTURED',
            'trans_id' => null, 'ref' => null,
            'payment_id' => $payment->payment_id, 'track_id' => $payment->track_id,
            'amt' => null, 'raw' => [],
        ]);

        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame(PaymentTransaction::STATUS_FAILED, $payment->fresh()->status);
        $this->assertSame(Appointment::STATUS_CANCELLED, $payment->appointment->fresh()->status);
    }

    public function test_a_payment_the_bank_never_saw_is_left_alone(): void
    {
        // Someone opened the page and closed it. Nothing was charged, so there
        // is nothing to settle — and marking it failed would be a guess.
        $payment = $this->pendingCardBooking();
        $this->gatewayReturning(['found' => false, 'captured' => false, 'result' => null,
            'trans_id' => null, 'ref' => null, 'payment_id' => null,
            'track_id' => null, 'amt' => null, 'raw' => []]);

        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame(PaymentTransaction::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(Appointment::STATUS_PENDING, $payment->appointment->fresh()->status);
    }

    public function test_a_payment_inside_the_grace_window_is_not_touched(): void
    {
        // The customer may still be on the bank's page; the redirect is about
        // to settle it properly and this must not race it.
        $payment = $this->pendingCardBooking(age: '-2 minutes');
        $this->gatewayReturning($this->captured($payment));

        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame(PaymentTransaction::STATUS_PENDING, $payment->fresh()->status);
    }

    public function test_a_dry_run_reports_without_changing_anything(): void
    {
        $payment = $this->pendingCardBooking();
        $this->gatewayReturning($this->captured($payment));

        $this->artisan('payments:reconcile', ['--dry-run' => true])->assertSuccessful();

        $this->assertSame(PaymentTransaction::STATUS_PENDING, $payment->fresh()->status);
        $this->assertSame(Appointment::STATUS_PENDING, $payment->appointment->fresh()->status);
    }

    public function test_settling_twice_does_not_double_count(): void
    {
        // The sweep can race the webhook. Both arriving must not spend the seat
        // twice or credit anything twice.
        $payment = $this->pendingCardBooking();
        $this->gatewayReturning($this->captured($payment));

        $this->artisan('payments:reconcile')->assertSuccessful();
        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame(1, $payment->appointment->timeSlot->fresh()->booked_count);
    }

    public function test_it_does_nothing_when_the_gateway_is_not_configured(): void
    {
        $this->mock(ArbGateway::class, fn ($mock) => $mock->shouldReceive('isConfigured')->andReturn(false));

        $this->artisan('payments:reconcile')
            ->expectsOutputToContain('not configured')
            ->assertSuccessful();
    }

    public function test_a_lookup_failure_does_not_stop_the_sweep(): void
    {
        // One unreachable inquiry must leave the row for the next run rather
        // than aborting every other row behind it.
        $payment = $this->pendingCardBooking();

        $this->mock(ArbGateway::class, function ($mock) {
            $mock->shouldReceive('isConfigured')->andReturn(true);
            $mock->shouldReceive('inquire')->andThrow(new \RuntimeException('gateway down'));
        });

        $this->artisan('payments:reconcile')->assertSuccessful();

        $this->assertSame(PaymentTransaction::STATUS_PENDING, $payment->fresh()->status);
    }
}
