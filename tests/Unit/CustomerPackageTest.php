<?php

namespace Tests\Unit;

use App\Models\CustomerPackage;
use Tests\TestCase;

/**
 * Spendability rules for a prepaid plan. Expiry is evaluated live rather than
 * read off the stored status, so a plan that lapsed since its last write is
 * refused even before any sweep runs.
 */
class CustomerPackageTest extends TestCase
{
    private function plan(array $overrides = []): CustomerPackage
    {
        $plan = new CustomerPackage;
        foreach (array_merge([
            'visits_total' => 10,
            'visits_used' => 0,
            'payment_status' => 'paid',
            'status' => CustomerPackage::STATUS_ACTIVE,
            'expires_at' => now()->addDays(30),
        ], $overrides) as $key => $value) {
            $plan->{$key} = $value;
        }

        return $plan;
    }

    public function test_an_active_paid_plan_with_visits_is_usable(): void
    {
        $plan = $this->plan();

        $this->assertTrue($plan->isUsable());
        $this->assertSame(10, $plan->visitsRemaining());
    }

    public function test_a_plan_with_every_visit_spent_is_not_usable(): void
    {
        $plan = $this->plan(['visits_used' => 10]);

        $this->assertSame(0, $plan->visitsRemaining());
        $this->assertFalse($plan->isUsable());
    }

    public function test_visits_remaining_never_goes_negative(): void
    {
        $plan = $this->plan(['visits_used' => 12]);

        $this->assertSame(0, $plan->visitsRemaining());
    }

    public function test_an_expired_plan_is_not_usable_even_while_marked_active(): void
    {
        $plan = $this->plan(['expires_at' => now()->subDay()]);

        $this->assertTrue($plan->hasExpired());
        $this->assertFalse($plan->isUsable());
        $this->assertSame(
            CustomerPackage::STATUS_EXPIRED,
            $plan->effectiveStatus(),
            'a lapsed plan must read as expired before any sweep rewrites it',
        );
    }

    public function test_an_unpaid_plan_is_not_usable(): void
    {
        $plan = $this->plan([
            'payment_status' => 'pending',
            'status' => CustomerPackage::STATUS_PENDING,
        ]);

        $this->assertFalse($plan->isUsable(),
            'a card plan must not be spendable while the customer is still paying');
    }

    public function test_a_cancelled_plan_is_not_usable(): void
    {
        $plan = $this->plan(['status' => CustomerPackage::STATUS_CANCELLED]);

        $this->assertFalse($plan->isUsable());
    }

    public function test_a_plan_without_an_expiry_never_lapses(): void
    {
        $plan = $this->plan(['expires_at' => null]);

        $this->assertFalse($plan->hasExpired());
        $this->assertTrue($plan->isUsable());
    }
}
