<?php

namespace Tests\Feature;

use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Services\Notifications\PushSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

/**
 * The sender used to log only failures, so a silent log could mean "every
 * device accepted" or "nothing was ever sent" — indistinguishable, and that
 * is exactly the question asked when a customer reports a missing push.
 */
class PushBatchSummaryTest extends TestCase
{
    use RefreshDatabase;

    private string $credentials;

    protected function setUp(): void
    {
        parent::setUp();

        // configured() only checks the file exists; the OAuth exchange is
        // short-circuited by seeding the token cache below.
        $this->credentials = tempnam(sys_get_temp_dir(), 'sa').'.json';
        file_put_contents($this->credentials, '{}');

        config([
            'services.fcm.project' => 'velto-test',
            'services.fcm.credentials' => $this->credentials,
        ]);

        Cache::put('fcm.access_token.customer', 'test-token', now()->addMinutes(5));
    }

    protected function tearDown(): void
    {
        @unlink($this->credentials);
        parent::tearDown();
    }

    public function test_a_fully_accepted_batch_logs_the_delivered_count(): void
    {
        Http::fake(['fcm.googleapis.com/*' => Http::response(['name' => 'projects/velto-test/messages/1'], 200)]);

        $logged = [];
        Log::listen(function ($message) use (&$logged) {
            if ($message->message === '[push] batch') {
                $logged[] = $message->context;
            }
        });

        app(PushSender::class)->send(['tok-a', 'tok-b'], 'Title', 'Body');

        $this->assertCount(1, $logged);
        $this->assertSame(2, $logged[0]['delivered']);
        $this->assertSame(0, $logged[0]['failed']);
        $this->assertNull($logged[0]['statuses']);
    }

    public function test_failures_are_counted_by_status_and_dead_tokens_pruned(): void
    {
        $customer = Customer::create(['phone' => '+966512345678', 'name' => 'T', 'status' => 'active']);
        CustomerDevice::create(['customer_id' => $customer->id, 'fcm_token' => 'dead-token', 'platform' => 'ios']);

        Http::fake([
            'fcm.googleapis.com/*' => Http::sequence()
                ->push(['name' => 'ok'], 200)
                ->push(['error' => ['status' => 'NOT_FOUND', 'message' => 'Requested entity was not found.']], 404)
                ->push(['error' => ['status' => 'UNAUTHENTICATED', 'message' => 'Invalid APNs credential.']], 401),
        ]);

        $logged = [];
        Log::listen(function ($message) use (&$logged) {
            if ($message->message === '[push] batch') {
                $logged[] = $message->context;
            }
        });

        app(PushSender::class)->send(['good-token', 'dead-token', 'bad-apns'], 'Title', 'Body');

        $this->assertSame(1, $logged[0]['delivered']);
        $this->assertSame(2, $logged[0]['failed']);
        $this->assertSame(['NOT_FOUND' => 1, 'UNAUTHENTICATED' => 1], $logged[0]['statuses']);

        // The dead one is gone; the APNs-auth one is kept — that failure is
        // ours, not the device's.
        $this->assertDatabaseMissing('customer_devices', ['fcm_token' => 'dead-token']);
    }
}
