<?php

namespace Tests\Feature;

use App\Models\PromoCode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The admin form sends an explicit null for a field the user cleared, and
 * MySQL rejects that against a NOT NULL column whose default only applies
 * when the column is omitted. Three real save attempts failed this way.
 */
class PromoCodeBlankDefaultsTest extends TestCase
{
    use RefreshDatabase;

    public function test_blank_minimum_order_and_per_customer_limit_fall_back_to_defaults(): void
    {
        $promo = PromoCode::create([
            'code' => 'ESSA',
            'type' => PromoCode::TYPE_FIXED,
            'value' => 10,
            'min_order_total' => null,
            'per_customer_limit' => null,
            'is_active' => true,
        ]);

        $this->assertSame('0.00', (string) $promo->fresh()->min_order_total);
        $this->assertSame(1, $promo->fresh()->per_customer_limit);
    }

    public function test_empty_string_is_treated_as_blank_too(): void
    {
        $promo = PromoCode::create([
            'code' => 'BLANKSTR',
            'type' => PromoCode::TYPE_FIXED,
            'value' => 5,
            'min_order_total' => '',
            'per_customer_limit' => '',
            'is_active' => true,
        ]);

        $this->assertSame('0.00', (string) $promo->fresh()->min_order_total);
        $this->assertSame(1, $promo->fresh()->per_customer_limit);
    }

    public function test_real_values_are_untouched(): void
    {
        $promo = PromoCode::create([
            'code' => 'REAL50',
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 20,
            'min_order_total' => 50,
            'per_customer_limit' => 3,
            'is_active' => true,
        ]);

        $this->assertSame('50.00', (string) $promo->fresh()->min_order_total);
        $this->assertSame(3, $promo->fresh()->per_customer_limit);
    }
}
