<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\PromoCode;
use App\Models\TimeSlot;
use App\Models\Vehicle;
use App\Models\WashPackage;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The team created a campaign code in Arabic, العويس, and every customer who
 * typed it got a validation error: the API only allowed A-Z and digits, so
 * the code was never even looked up. Codes may now be written in any script.
 */
class ArabicPromoCodeTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'name' => 'Customer', 'phone' => '+966500000001', 'status' => 'active',
            'city' => 'Riyadh', 'preferred_language' => 'ar', 'wallet_balance' => 500,
        ]);

        PromoCode::create([
            'code' => 'العويس',
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 15,
            'max_discount' => 7,
            'min_order_total' => 0,
            'per_customer_limit' => 1,
            'usage_limit' => 100,
            'used_count' => 0,
            'is_active' => true,
        ]);
    }

    public function test_an_arabic_code_previews_as_valid(): void
    {
        $this->actingAs($this->customer, 'customer')
            ->postJson('/api/v1/me/promo/preview', ['code' => 'العويس', 'subtotal' => 35])
            ->assertOk()
            ->assertJsonPath('data.valid', true)
            ->assertJsonPath('data.discount', 5.25);
    }

    public function test_an_arabic_code_is_applied_when_booking(): void
    {
        $package = WashPackage::create([
            'name' => 'Express exterior wash', 'name_ar' => 'غسيل خارجي سريع',
            'type' => 'single', 'price' => 35, 'duration_minutes' => 30, 'is_active' => true,
        ]);
        $vehicle = Vehicle::create([
            'customer_id' => $this->customer->id, 'name' => 'My car', 'brand' => 'Toyota',
            'model' => 'Camry', 'plate' => 'ABC 1234', 'is_default' => true,
        ]);
        $moment = CarbonImmutable::now(config('app.business_timezone'))->addMinutes(90);
        $slot = TimeSlot::create([
            'date' => $moment->toDateString(), 'start_time' => $moment->format('H:i:s'),
            'end_time' => $moment->addHour()->format('H:i:s'), 'capacity' => 5,
            'booked_count' => 0, 'is_active' => true,
        ]);

        $this->actingAs($this->customer, 'customer')->postJson('/api/v1/me/appointments', [
            'wash_package_id' => $package->id,
            'vehicle_id' => $vehicle->id,
            'time_slot_id' => $slot->id,
            'payment_method' => 'wallet',
            'promo_code' => 'العويس',
        ])->assertCreated();

        $this->assertSame(1, PromoCode::where('code', 'العويس')->value('used_count'));
    }

    public function test_spaces_and_symbols_are_still_refused(): void
    {
        // Opening the alphabet is not opening the door to arbitrary input.
        foreach (['العويس 15', "VELTO'15", 'A;B', '<b>x</b>'] as $code) {
            $this->actingAs($this->customer, 'customer')
                ->postJson('/api/v1/me/promo/preview', ['code' => $code, 'subtotal' => 35])
                ->assertStatus(422);
        }
    }
}
