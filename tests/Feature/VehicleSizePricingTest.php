<?php

namespace Tests\Feature;

use App\Http\Resources\Api\V1\VehicleResource;
use App\Models\Customer;
use App\Models\VehicleBrand;
use App\Models\VehicleCategory;
use App\Models\VehicleModelEntry;
use App\Models\WashPackage;
use App\Services\Booking\BookingFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

/**
 * A wash is priced by the size of the car, not by the service. These pin the
 * two halves that decide what a customer is charged: which band a free-typed
 * vehicle resolves to, and what happens when it resolves to nothing.
 */
class VehicleSizePricingTest extends TestCase
{
    use RefreshDatabase;

    private WashPackage $package;

    protected function setUp(): void
    {
        parent::setUp();

        $bands = [
            'A' => ['Large', 45],
            'B' => ['Medium', 30],
            'C' => ['Small', 20],
        ];

        foreach ($bands as $code => [$name, $price]) {
            VehicleCategory::create([
                'code' => $code,
                'name' => $name,
                'price' => $price,
                'is_active' => true,
            ]);
        }

        // Two brands sharing a model name, which is the case that makes
        // matching on the model alone wrong.
        $lexus = VehicleBrand::create(['name' => 'Lexus', 'name_ar' => 'لكزس', 'slug' => 'lexus', 'is_active' => true]);
        $toyota = VehicleBrand::create(['name' => 'Toyota', 'name_ar' => 'تويوتا', 'slug' => 'toyota', 'is_active' => true]);

        $band = fn (string $code) => VehicleCategory::where('code', $code)->value('id');

        VehicleModelEntry::create([
            'vehicle_brand_id' => $lexus->id,
            'vehicle_category_id' => $band('A'),
            'name' => 'LX',
            'is_active' => true,
        ]);
        VehicleModelEntry::create([
            'vehicle_brand_id' => $toyota->id,
            'vehicle_category_id' => $band('C'),
            'name' => 'LX',
            'is_active' => true,
        ]);
        VehicleModelEntry::create([
            'vehicle_brand_id' => $toyota->id,
            'vehicle_category_id' => $band('B'),
            'name' => 'Camry',
            'name_ar' => 'كامري',
            'is_active' => true,
        ]);
        // Known to the catalogue but not yet classified.
        VehicleModelEntry::create([
            'vehicle_brand_id' => $toyota->id,
            'vehicle_category_id' => null,
            'name' => 'Supra',
            'is_active' => true,
        ]);

        $this->package = WashPackage::create([
            'name' => 'Full detail',
            'type' => 'single',
            'price' => 40,
            'duration_minutes' => 60,
            'is_active' => true,
        ]);
    }

    private function basePriceFor(string $brand, string $model): float
    {
        $customer = Customer::create([
            'name' => 'Hani',
            'phone' => '+9665'.random_int(10_000_000, 99_999_999),
            'status' => 'active',
            'wallet_balance' => 0,
            'joined_at' => now(),
        ]);

        $vehicle = $customer->vehicles()->create([
            'brand' => $brand,
            'model' => $model,
            'plate' => 'ABC 1234',
        ]);

        $booking = app(BookingFactory::class)->resolveBooking($customer, [
            'vehicle_id' => $vehicle->id,
            'wash_package_id' => $this->package->id,
        ]);

        return (float) $booking['base'];
    }

    public function test_each_band_charges_its_own_price(): void
    {
        $this->assertSame(30.0, $this->basePriceFor('Toyota', 'Camry'));
        $this->assertSame(45.0, $this->basePriceFor('Lexus', 'LX'));
    }

    public function test_a_shared_model_name_is_priced_off_the_right_brand(): void
    {
        // Both brands sell an "LX"; they sit in different bands.
        $this->assertSame(45.0, $this->basePriceFor('Lexus', 'LX'));
        $this->assertSame(20.0, $this->basePriceFor('Toyota', 'LX'));
    }

    public function test_matching_ignores_case_and_padding(): void
    {
        $this->assertSame(30.0, $this->basePriceFor('  toyota ', 'CAMRY'));
    }

    public function test_an_arabic_brand_or_model_still_resolves(): void
    {
        $this->assertSame(30.0, $this->basePriceFor('تويوتا', 'Camry'));
        $this->assertSame(30.0, $this->basePriceFor('Toyota', 'كامري'));
    }

    public function test_an_unknown_model_falls_back_to_the_package_price(): void
    {
        // Typed free-hand; the catalogue has never heard of it.
        $this->assertSame(40.0, $this->basePriceFor('Lexus', 'ES350'));
    }

    public function test_an_unclassified_model_falls_back_to_the_package_price(): void
    {
        // In the catalogue, but no band — must not be treated as the cheapest.
        $this->assertSame(40.0, $this->basePriceFor('Toyota', 'Supra'));
    }

    public function test_a_band_with_no_price_falls_back_to_the_package_price(): void
    {
        VehicleCategory::where('code', 'B')->update(['price' => null]);

        $this->assertSame(40.0, $this->basePriceFor('Toyota', 'Camry'));
    }

    public function test_the_vehicle_payload_carries_the_band_it_prices_off(): void
    {
        $customer = Customer::create([
            'name' => 'Hani',
            'phone' => '+966512345678',
            'status' => 'active',
            'wallet_balance' => 0,
            'joined_at' => now(),
        ]);

        $priced = $customer->vehicles()->create([
            'brand' => 'Toyota', 'model' => 'Camry', 'plate' => 'ABC 1234',
        ]);
        $unplaceable = $customer->vehicles()->create([
            'brand' => 'Lexus', 'model' => 'ES350', 'plate' => 'XYZ 9876',
        ]);

        $request = Request::create('/');

        $payload = (new VehicleResource($priced))->toArray($request);
        $this->assertSame('B', $payload['category']['code']);
        $this->assertSame(30.0, $payload['category']['price']);

        // Null, not a zero price the client might charge against.
        $payload = (new VehicleResource($unplaceable))->toArray($request);
        $this->assertNull($payload['category']);
    }
}
