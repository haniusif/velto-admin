<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Sign-in matches `phone` exactly against the canonical form the apps send, so
 * a row saved in any other shape is unreachable: the person requests a code,
 * receives one, and is told no account exists.
 *
 * Worker 7 was created through the admin panel as "537938734" and could never
 * sign in. The API normalised on the way in; Filament forms, seeders and tinker
 * did not. These cover the paths that bypass the API.
 */
class PhoneNormalisationTest extends TestCase
{
    use RefreshDatabase;

    public static function spellings(): array
    {
        return [
            'bare national' => ['537938734'],
            'local trunk prefix' => ['0537938734'],
            'E.164' => ['+966537938734'],
            'dial code, no plus' => ['966537938734'],
            'international access code' => ['00966537938734'],
            'spaced as pasted' => ['+966 53 793 8734'],
            'dashed' => ['053-793-8734'],
        ];
    }

    #[DataProvider('spellings')]
    public function test_a_worker_is_stored_canonically_however_it_was_typed(string $typed): void
    {
        $worker = Worker::create(['name' => 'الفاس', 'phone' => $typed, 'status' => 'active']);

        $this->assertSame('+966537938734', $worker->fresh()->phone);
    }

    #[DataProvider('spellings')]
    public function test_a_customer_is_stored_canonically_however_it_was_typed(string $typed): void
    {
        $customer = Customer::create(['name' => 'Test', 'phone' => $typed, 'status' => 'active']);

        $this->assertSame('+966537938734', $customer->fresh()->phone);
    }

    public function test_the_row_is_findable_by_the_form_the_apps_send(): void
    {
        Worker::create(['name' => 'الفاس', 'phone' => '537938734', 'status' => 'active']);

        // This is the exact lookup WorkerAuthController performs.
        $this->assertTrue(
            Worker::where('phone', '+966537938734')->where('status', 'active')->exists(),
            'a worker saved without the dial code must still be reachable at sign-in',
        );
    }

    public function test_updating_an_existing_row_normalises_too(): void
    {
        $worker = Worker::create(['name' => 'الفاس', 'phone' => '+966500000001', 'status' => 'active']);

        $worker->update(['phone' => '0537938734']);

        $this->assertSame('+966537938734', $worker->fresh()->phone);
    }

    /**
     * A value that is not a Saudi mobile is kept as typed rather than blanked,
     * so a bad import stays visible in the panel instead of vanishing.
     */
    public function test_a_non_saudi_number_is_left_alone_not_discarded(): void
    {
        $worker = Worker::create(['name' => 'Test', 'phone' => '+201012345678', 'status' => 'active']);

        $this->assertSame('+201012345678', $worker->fresh()->phone);
    }

    public function test_an_empty_phone_is_not_invented(): void
    {
        $customer = Customer::create(['name' => 'Test', 'phone' => '', 'status' => 'active']);

        $this->assertSame('', $customer->fresh()->phone);
    }
}
