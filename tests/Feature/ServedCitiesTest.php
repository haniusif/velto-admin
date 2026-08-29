<?php

namespace Tests\Feature;

use App\Models\City;
use Database\Seeders\CitySeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Velto serves four cities. The table was seeded with ten, all switched on, so
 * the app offered seven places nobody could be sent to — and did not offer
 * Yanbu at all, because it was never seeded.
 */
class ServedCitiesTest extends TestCase
{
    use RefreshDatabase;

    private const SERVED = ['Riyadh', 'Jeddah', 'Taif', 'Yanbu'];

    protected function setUp(): void
    {
        parent::setUp();

        // The migration covers databases that already exist; the seeder covers
        // new ones. Both must land on the same four cities, so the tests run
        // against the seeded table.
        $this->seed(CitySeeder::class);
    }

    private function activeCityNames(): array
    {
        return City::where('is_active', true)->orderBy('name')->pluck('name')->all();
    }

    public function test_exactly_the_four_served_cities_are_active(): void
    {
        $active = $this->activeCityNames();

        sort($active);
        $expected = self::SERVED;
        sort($expected);

        $this->assertSame($expected, $active);
    }

    public function test_yanbu_exists_and_is_reachable(): void
    {
        // The fourth city could not be chosen at all: it was never seeded.
        $yanbu = City::where('name', 'Yanbu')->first();

        $this->assertNotNull($yanbu, 'Yanbu is missing from the cities table');
        $this->assertTrue((bool) $yanbu->is_active);
        $this->assertSame('ينبع', $yanbu->name_ar);
        // Coordinates matter: the admin map centres on them.
        $this->assertNotNull($yanbu->latitude);
        $this->assertNotNull($yanbu->longitude);
    }

    public function test_the_unserved_cities_are_switched_off_not_deleted(): void
    {
        // Dammam carries areas, and switching a row back on is how coverage
        // gets restored when it expands.
        foreach (['Mecca', 'Medina', 'Dammam', 'Khobar', 'Dhahran', 'Tabuk', 'Abha'] as $name) {
            $city = City::where('name', $name)->first();

            $this->assertNotNull($city, "{$name} was deleted rather than switched off");
            $this->assertFalse((bool) $city->is_active, "{$name} is still being offered");
        }
    }

    public function test_the_api_offers_only_the_served_cities(): void
    {
        // What the app and the web build actually read.
        $names = collect($this->getJson('/api/v1/locations/cities')->assertOk()->json('data'))
            ->pluck('name')
            ->sort()
            ->values()
            ->all();

        $expected = self::SERVED;
        sort($expected);

        $this->assertSame($expected, $names);
    }
}
