<?php

namespace Tests\Feature;

use App\Filament\Resources\CustomerNotifications\Pages\CreateCustomerNotification;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\CustomerNotification;
use App\Services\Notifications\NotificationDispatcher;
use App\Services\Notifications\PushSender;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionClass;
use Tests\TestCase;

/**
 * Sending from the admin used to write the inbox row and stop there, so it
 * looked delivered while the customer's phone stayed silent.
 *
 * The admin page is driven directly rather than through Livewire: a Filament
 * resource page will not mount under Livewire::test() in this suite, so the
 * hook is invoked the same way the framework invokes it.
 */
class SingleNotificationPushTest extends TestCase
{
    use RefreshDatabase;

    private RecordingPushSender $push;

    protected function setUp(): void
    {
        parent::setUp();

        $this->push = new RecordingPushSender;
        $this->app->instance(PushSender::class, $this->push);
    }

    private function customer(string $language = 'ar', bool $withDevice = true): Customer
    {
        static $n = 0;
        $n++;

        $customer = Customer::create([
            'name' => "Customer {$n}",
            'phone' => '+96650000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => $language,
        ]);

        if ($withDevice) {
            CustomerDevice::create([
                'customer_id' => $customer->id,
                'fcm_token' => "token-{$n}",
                'platform' => 'ios',
            ]);
        }

        return $customer;
    }

    private function notification(Customer $customer, array $overrides = []): CustomerNotification
    {
        return CustomerNotification::create(array_merge([
            'customer_id' => $customer->id,
            'kind' => CustomerNotification::KIND_PROMO,
            'title' => 'Half price',
            'title_ar' => 'نصف السعر',
            'body' => 'Book today.',
            'body_ar' => 'احجز اليوم.',
        ], $overrides));
    }

    /** Runs the admin page's post-create hook exactly as Filament would. */
    private function runAfterCreate(CustomerNotification $record): void
    {
        $page = new CreateCustomerNotification;
        $page->record = $record;

        $hook = (new ReflectionClass($page))->getMethod('afterCreate');
        $hook->setAccessible(true);
        $hook->invoke($page);
    }

    public function test_the_admin_create_hook_pushes_to_the_customers_devices(): void
    {
        $customer = $this->customer();

        $this->runAfterCreate($this->notification($customer));

        $this->assertCount(1, $this->push->sent);
        $this->assertSame(['token-1'], $this->push->sent[0]['tokens']);
        $this->assertSame(PushSender::AUDIENCE_CUSTOMER, $this->push->sent[0]['audience']);
    }

    public function test_the_admin_create_hook_does_not_write_a_second_inbox_row(): void
    {
        $customer = $this->customer();

        $this->runAfterCreate($this->notification($customer));

        // The page already persisted one row; pushing must not add another.
        $this->assertSame(1, CustomerNotification::query()->count());
    }

    public function test_an_arabic_customer_gets_the_arabic_copy(): void
    {
        $customer = $this->customer('ar');

        app(NotificationDispatcher::class)
            ->pushCustomerNotification($this->notification($customer));

        $this->assertSame('نصف السعر', $this->push->sent[0]['title']);
        $this->assertSame('احجز اليوم.', $this->push->sent[0]['body']);
    }

    public function test_an_english_customer_gets_the_english_copy(): void
    {
        $customer = $this->customer('en');

        app(NotificationDispatcher::class)
            ->pushCustomerNotification($this->notification($customer));

        $this->assertSame('Half price', $this->push->sent[0]['title']);
    }

    public function test_it_falls_back_to_english_when_the_arabic_copy_is_blank(): void
    {
        $customer = $this->customer('ar');

        app(NotificationDispatcher::class)->pushCustomerNotification(
            $this->notification($customer, ['title_ar' => '', 'body_ar' => '']),
        );

        // An empty banner is worse than the wrong language.
        $this->assertSame('Half price', $this->push->sent[0]['title']);
        $this->assertSame('Book today.', $this->push->sent[0]['body']);
    }

    public function test_a_customer_with_no_device_reports_that_nothing_was_pushed(): void
    {
        $customer = $this->customer(withDevice: false);

        $pushed = app(NotificationDispatcher::class)
            ->pushCustomerNotification($this->notification($customer));

        $this->assertFalse($pushed);
        $this->assertCount(0, $this->push->sent);
    }

    public function test_the_automatic_path_still_writes_one_row_and_pushes_once(): void
    {
        $customer = $this->customer();

        app(NotificationDispatcher::class)->customerAnnouncement(
            [$customer->id], CustomerNotification::KIND_PROMO,
            'Half price', 'نصف السعر', 'Book today.', 'احجز اليوم.',
        );

        $this->assertSame(1, CustomerNotification::query()->count());
        $this->assertCount(1, $this->push->sent);
    }
}

/** Captures sends instead of talking to FCM. */
class RecordingPushSender extends PushSender
{
    /** @var array<int,array<string,mixed>> */
    public array $sent = [];

    public function configured(string $audience = self::AUDIENCE_CUSTOMER): bool
    {
        return true;
    }

    public function send(
        array $tokens, string $title, string $body,
        array $data = [], string $audience = self::AUDIENCE_CUSTOMER,
    ): void {
        $this->sent[] = compact('tokens', 'title', 'body', 'data', 'audience');
    }
}
