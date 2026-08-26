<?php

namespace Tests\Feature;

use App\Filament\Widgets\PaymentsOverview;
use App\Models\Customer;
use App\Models\PaymentTransaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

/**
 * The dashboard's Pending tile counted every unfinished checkout ever made and
 * sat between three tiles showing the current month. It read 34 while the
 * month held 27, and its oldest entry was seven weeks old — a number that only
 * ever grew and told nobody anything.
 */
class PaymentsOverviewTest extends TestCase
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
        ]);
    }

    private function transaction(string $status, string $createdAt, float $amount = 40, string $purpose = 'booking'): PaymentTransaction
    {
        $row = PaymentTransaction::create([
            'customer_id' => $this->customer()->id,
            'gateway' => 'arb',
            'action' => 'purchase',
            'purpose' => $purpose,
            'status' => $status,
            'amount' => $amount,
            'currency' => 'SAR',
            'track_id' => 'T'.uniqid(),
        ]);

        // created_at is set by the model, so it is forced afterwards.
        $row->forceFill(['created_at' => $createdAt])->save();

        return $row->refresh();
    }

    /** @return array<string,string> label => value */
    private function stats(): array
    {
        $method = (new ReflectionClass(PaymentsOverview::class))->getMethod('getStats');
        $method->setAccessible(true);

        $out = [];
        foreach ($method->invoke(new PaymentsOverview) as $stat) {
            $out[$stat->getLabel()] = (string) $stat->getValue();
        }

        return $out;
    }

    public function test_a_checkout_still_in_progress_is_counted(): void
    {
        // Someone on the bank's page right now — the only pending row anyone
        // can act on.
        $this->transaction(PaymentTransaction::STATUS_PENDING, now()->subMinutes(5)->toDateTimeString());

        $this->assertSame('1', $this->stats()[__('Awaiting payment')]);
    }

    public function test_an_abandoned_checkout_is_not_counted_as_awaiting(): void
    {
        // Past the grace window the booking has already been released; the row
        // is a record of someone who changed their mind.
        $this->transaction(PaymentTransaction::STATUS_PENDING, now()->subHours(3)->toDateTimeString());

        $this->assertSame('0', $this->stats()[__('Awaiting payment')]);
    }

    public function test_last_months_abandoned_checkouts_do_not_leak_in(): void
    {
        // The actual defect: the tile reached back to July while its
        // neighbours showed August.
        $this->transaction(PaymentTransaction::STATUS_PENDING, now()->subMonths(2)->toDateTimeString());

        $this->assertSame('0', $this->stats()[__('Awaiting payment')]);
        // Nor into the abandoned figure beside it, which is also monthly.
        $this->assertStringContainsString(__('Abandoned').': 0', $this->description());
    }

    public function test_captured_covers_this_month_only(): void
    {
        $this->transaction(PaymentTransaction::STATUS_CAPTURED, now()->subDays(2)->toDateTimeString(), 100);
        $this->transaction(PaymentTransaction::STATUS_CAPTURED, now()->subMonths(2)->toDateTimeString(), 999);

        $this->assertStringContainsString('100.00', $this->stats()[__('Captured')]);
        $this->assertStringNotContainsString('1,099', $this->stats()[__('Captured')]);
    }

    public function test_amounts_are_labelled_in_riyals(): void
    {
        $this->transaction(PaymentTransaction::STATUS_CAPTURED, now()->subDay()->toDateTimeString(), 40);

        $this->assertStringContainsString(__('SAR'), $this->stats()[__('Captured')]);
    }

    /** The description on the pending tile, where abandoned and failed live. */
    private function description(): string
    {
        $method = (new ReflectionClass(PaymentsOverview::class))->getMethod('getStats');
        $method->setAccessible(true);

        foreach ($method->invoke(new PaymentsOverview) as $stat) {
            if ($stat->getLabel() === __('Awaiting payment')) {
                return (string) $stat->getDescription();
            }
        }

        return '';
    }

    public function test_abandoned_and_failed_are_reported_for_the_month(): void
    {
        $this->transaction(PaymentTransaction::STATUS_PENDING, now()->subHours(4)->toDateTimeString());
        $this->transaction(PaymentTransaction::STATUS_PENDING, now()->subMonths(2)->toDateTimeString());
        $this->transaction(PaymentTransaction::STATUS_FAILED, now()->subDays(3)->toDateTimeString());
        $this->transaction(PaymentTransaction::STATUS_FAILED, now()->subMonths(2)->toDateTimeString());

        $description = $this->description();

        // One of each from this month; the two from July are excluded.
        $this->assertStringContainsString(__('Abandoned').': 1', $description);
        $this->assertStringContainsString(__('Failed').': 1', $description);
    }
}
