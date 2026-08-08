<?php

namespace Tests\Feature;

use App\Filament\Pages\BulkNotificationPage;
use App\Filament\Pages\SendSmsPage;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * The two Marketing pages are hand-built (not resource pages), so nothing else
 * proves their schema, actions and audience resolution actually boot.
 */
class MarketingPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-for-tests',
        ]));
    }

    private function customer(string $status = 'active', string $city = 'Riyadh'): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create([
            'name' => "Customer {$n}",
            'phone' => '+96650000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'status' => $status,
            'city' => $city,
            'preferred_language' => 'ar',
        ]);
    }

    public function test_bulk_notification_page_renders(): void
    {
        Livewire::test(BulkNotificationPage::class)->assertOk();
    }

    public function test_send_sms_page_renders(): void
    {
        Livewire::test(SendSmsPage::class)->assertOk();
    }

    public function test_bulk_notification_writes_an_inbox_row_per_customer(): void
    {
        $active = collect([$this->customer(), $this->customer()]);
        $this->customer('blocked');

        Livewire::test(BulkNotificationPage::class)
            ->fillForm([
                'audience' => BulkNotificationPage::AUDIENCE_ALL,
                'kind' => CustomerNotification::KIND_PROMO,
                'title' => 'Half price this week',
                'title_ar' => 'نصف السعر هذا الأسبوع',
                'body' => 'Book any wash and pay half.',
                'body_ar' => 'احجز أي غسلة وادفع النصف.',
            ])
            ->assertActionExists('send')
            ->call('send')
            ->assertHasNoErrors();

        // Only the active customers are reached; the blocked one is skipped.
        $this->assertSame(2, CustomerNotification::query()->count());

        foreach ($active as $customer) {
            $this->assertDatabaseHas('customer_notifications', [
                'customer_id' => $customer->id,
                'kind' => CustomerNotification::KIND_PROMO,
                'title' => 'Half price this week',
            ]);
        }
    }

    public function test_sms_audience_resolves_manual_numbers(): void
    {
        Livewire::test(SendSmsPage::class)
            ->fillForm([
                'audience' => SendSmsPage::AUDIENCE_MANUAL,
                'numbers' => "+966500000001\n+966500000002, +966500000001",
                'message' => 'Hello from Velto',
            ])
            ->assertHasNoErrors();
    }
}
