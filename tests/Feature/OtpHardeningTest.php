<?php

namespace Tests\Feature;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

/**
 * The OTP endpoints are where an attacker would start: a four-digit code is
 * guessable and every SMS costs money. These pin the guards in place.
 */
class OtpHardeningTest extends TestCase
{
    use RefreshDatabase;

    private const PHONE = '0512345678';
    private const E164 = '+966512345678';

    protected function setUp(): void
    {
        parent::setUp();
        // No SMS provider in tests → the "1111" dev code is issued and echoed.
        config(['services.jawaly.app_id' => null, 'services.jawaly.app_secret' => null]);
        RateLimiter::clear('otp-req:ip:127.0.0.1');
    }

    private function requestCode(string $phone = self::PHONE)
    {
        return $this->postJson('/api/v1/auth/request-otp', ['phone' => $phone]);
    }

    public function test_codes_are_stored_hashed_not_in_plain_text(): void
    {
        $this->requestCode()->assertOk()->assertJsonPath('data.dev_code', '1111');

        $stored = DB::table('phone_otps')->where('phone', self::E164)->value('code');

        $this->assertNotSame('1111', $stored);
        $this->assertSame(64, strlen($stored));
    }

    public function test_five_wrong_guesses_void_the_code(): void
    {
        $this->requestCode();

        foreach (['0000', '1234', '9999', '5555', '4321'] as $wrong) {
            $this->postJson('/api/v1/auth/verify-otp', ['phone' => self::PHONE, 'code' => $wrong])
                ->assertStatus(422)->assertJsonPath('code', 'invalid_code');
        }

        // The real code is dead now.
        $this->postJson('/api/v1/auth/verify-otp', ['phone' => self::PHONE, 'code' => '1111'])
            ->assertStatus(422);
    }

    public function test_a_used_code_cannot_be_replayed(): void
    {
        $this->requestCode();
        $this->postJson('/api/v1/auth/verify-otp', ['phone' => self::PHONE, 'code' => '1111'])->assertOk();
        $this->postJson('/api/v1/auth/verify-otp', ['phone' => self::PHONE, 'code' => '1111'])->assertStatus(422);
    }

    public function test_a_second_request_within_a_minute_is_throttled_with_retry_after(): void
    {
        $this->requestCode()->assertOk();
        $this->requestCode()->assertStatus(429)->assertHeader('Retry-After')->assertJsonPath('code', 'too_many_requests');
    }

    public function test_hourly_cap_per_phone(): void
    {
        for ($i = 0; $i < 5; $i++) {
            DB::table('phone_otps')->insert([
                'phone' => self::E164, 'code' => str_repeat('a', 64), 'expires_at' => now()->addMinutes(10),
                'attempts' => 0, 'used_at' => now(), 'created_at' => now()->subMinutes(5 + $i), 'updated_at' => now(),
            ]);
        }

        $this->requestCode()->assertStatus(429)->assertJsonPath('retry_after', 3600);
    }

    public function test_blocked_customers_cannot_sign_in_and_lose_their_tokens(): void
    {
        $customer = Customer::create(['phone' => self::E164, 'name' => 'Blocked', 'status' => 'blocked']);
        $token = $customer->createToken('t')->plainTextToken;

        $this->requestCode()->assertOk()->assertJsonPath('data.dev_code', null); // no code issued, nothing leaked
        $this->assertSame(0, DB::table('phone_otps')->where('phone', self::E164)->count());

        $this->withToken($token)->getJson('/api/v1/me/vehicles')->assertStatus(403)->assertJsonPath('code', 'account_blocked');
        // The guard caches the resolved user inside one test process; a real
        // second HTTP request starts cold, which is what this simulates.
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/v1/me/vehicles')->assertStatus(401);
    }

    public function test_tokens_expire_and_are_capped_per_customer(): void
    {
        $this->requestCode();
        $this->postJson('/api/v1/auth/verify-otp', ['phone' => self::PHONE, 'code' => '1111'])->assertOk();

        $customer = Customer::where('phone', self::E164)->firstOrFail();
        $this->assertNotNull($customer->tokens()->first()->expires_at);

        for ($i = 0; $i < 12; $i++) {
            $customer->createToken('old');
        }
        DB::table('phone_otps')->where('phone', self::E164)->update(['used_at' => null, 'created_at' => now()->subMinutes(2)]);
        $this->postJson('/api/v1/auth/verify-otp', ['phone' => self::PHONE, 'code' => '1111'])->assertOk();

        $this->assertSame(10, $customer->tokens()->count());
    }

    public function test_profile_rejects_markup_and_digits_in_names(): void
    {
        $customer = Customer::create(['phone' => self::E164, 'name' => 'x', 'status' => 'active']);
        $token = $customer->createToken('t')->plainTextToken;

        $this->withToken($token)->patchJson('/api/v1/auth/profile', ['name' => '<b>Hani</b>'])->assertStatus(422);
        $this->withToken($token)->patchJson('/api/v1/auth/profile', ['name' => 'Hani 123'])->assertStatus(422);
        $this->withToken($token)->patchJson('/api/v1/auth/profile', ['name' => "هاني   يوسف"])->assertOk()->assertJsonPath('data.name', 'هاني يوسف');
    }
}
