<?php

namespace Tests\Unit;

use App\Models\PromoCode;
use Tests\TestCase;

/**
 * Discount arithmetic and the validity window. The parts that decide how much
 * money is given away, so they get pinned rather than trusted.
 */
class PromoCodeTest extends TestCase
{
    private function code(array $overrides = []): PromoCode
    {
        $c = new PromoCode;
        foreach (array_merge([
            'code' => 'TEST',
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 20,
            'max_discount' => null,
            'min_order_total' => 0,
            'is_active' => true,
            'starts_at' => null,
            'expires_at' => null,
        ], $overrides) as $k => $v) {
            $c->{$k} = $v;
        }

        return $c;
    }

    public function test_percentage_of_the_subtotal(): void
    {
        $this->assertSame(8.0, $this->code(['value' => 20])->discountFor(40));
    }

    public function test_a_fixed_amount_is_taken_off(): void
    {
        $c = $this->code(['type' => PromoCode::TYPE_FIXED, 'value' => 5]);

        $this->assertSame(5.0, $c->discountFor(40));
    }

    public function test_percentage_is_capped_by_max_discount(): void
    {
        $c = $this->code(['value' => 20, 'max_discount' => 15]);

        $this->assertSame(15.0, $c->discountFor(200),
            'an uncapped percentage is how a 200 SAR order goes out for 160');
    }

    public function test_a_discount_never_exceeds_the_subtotal(): void
    {
        $c = $this->code(['type' => PromoCode::TYPE_FIXED, 'value' => 50]);

        $this->assertSame(20.0, $c->discountFor(20),
            'a discount must never turn into credit');
    }

    public function test_a_discount_never_goes_negative(): void
    {
        $c = $this->code(['type' => PromoCode::TYPE_FIXED, 'value' => 50]);

        $this->assertSame(0.0, $c->discountFor(0));
    }

    public function test_an_inactive_code_is_outside_its_window(): void
    {
        $this->assertFalse($this->code(['is_active' => false])->withinWindow());
    }

    public function test_an_expired_code_is_outside_its_window(): void
    {
        $this->assertFalse($this->code(['expires_at' => now()->subDay()])->withinWindow());
    }

    public function test_a_code_that_has_not_started_is_outside_its_window(): void
    {
        $this->assertFalse($this->code(['starts_at' => now()->addDay()])->withinWindow());
    }

    public function test_a_code_with_no_dates_is_always_within_its_window(): void
    {
        $this->assertTrue($this->code()->withinWindow());
    }
}
