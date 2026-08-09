<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * The force-update floors. A wrong value here locks every customer out of the
 * app, so the endpoint's failure modes matter more than its happy path.
 */
class AppVersionEndpointTest extends TestCase
{
    use RefreshDatabase;

    private function seedVersions(array $overrides = []): void
    {
        $values = array_merge([
            'latest_version' => '1.2.0',
            'latest_build' => '40',
            'minimum_version' => '1.1.0',
            'minimum_build' => '30',
            'store_url' => 'https://apps.apple.com/app/id6762532453',
        ], $overrides);

        // The migration seeds permissive defaults, so overwrite rather than insert.
        foreach ($values as $key => $value) {
            AppSetting::updateOrCreate(
                ['key' => "app_version.ios.{$key}"],
                ['group' => 'app_version', 'value' => $value, 'type' => 'string'],
            );
        }
    }

    public function test_it_returns_the_configured_floors_for_a_platform(): void
    {
        $this->seedVersions();

        $this->getJson('/api/v1/catalog/app-version?platform=ios')
            ->assertOk()
            ->assertJson(['data' => [
                'platform' => 'ios',
                'latest_version' => '1.2.0',
                'minimum_version' => '1.1.0',
                'latest_build' => 40,
                'minimum_build' => 30,
                'store_url' => 'https://apps.apple.com/app/id6762532453',
            ]]);
    }

    public function test_build_numbers_come_back_as_integers(): void
    {
        $this->seedVersions();

        $data = $this->getJson('/api/v1/catalog/app-version?platform=ios')->json('data');

        $this->assertIsInt($data['latest_build']);
        $this->assertIsInt($data['minimum_build']);
    }

    public function test_an_unknown_platform_returns_nulls_rather_than_an_error(): void
    {
        // The client treats a failed check as "do not block". A 4xx here would
        // be indistinguishable from a real outage on some clients.
        $this->getJson('/api/v1/catalog/app-version?platform=windows')
            ->assertOk()
            ->assertJson(['data' => ['latest_version' => null, 'minimum_version' => null]]);
    }

    public function test_a_missing_platform_parameter_returns_nulls(): void
    {
        $this->getJson('/api/v1/catalog/app-version')
            ->assertOk()
            ->assertJson(['data' => ['platform' => null, 'store_url' => null]]);
    }

    public function test_unconfigured_settings_return_nulls_and_never_force_an_update(): void
    {
        // An unconfigured platform must not read as "minimum build 0", which
        // would force every install on earth to update.
        AppSetting::query()->where('key', 'like', 'app_version.android.%')->delete();

        $data = $this->getJson('/api/v1/catalog/app-version?platform=android')->json('data');

        $this->assertNull($data['minimum_version']);
        $this->assertNull($data['minimum_build']);
    }

    public function test_the_shipped_defaults_force_nobody(): void
    {
        // Whatever the migration seeds must be harmless on the day it runs:
        // every real install has to sit at or above the floor.
        $data = $this->getJson('/api/v1/catalog/app-version?platform=ios')->json('data');

        $this->assertSame('1.0.0', $data['minimum_version']);
        $this->assertSame(1, $data['minimum_build']);
    }

    public function test_a_non_numeric_build_is_treated_as_unset(): void
    {
        $this->seedVersions(['minimum_build' => 'not-a-number']);

        $data = $this->getJson('/api/v1/catalog/app-version?platform=ios')->json('data');

        $this->assertNull($data['minimum_build']);
    }

    public function test_the_endpoint_is_public(): void
    {
        // The check runs before sign-in, so it cannot sit behind auth.
        $this->getJson('/api/v1/catalog/app-version?platform=ios')->assertOk();
    }
}
