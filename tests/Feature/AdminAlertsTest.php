<?php

namespace Tests\Feature;

use App\Jobs\SendAdminAlert;
use App\Models\Appointment;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\User;
use App\Models\Worker;
use App\Services\Dispatch\DispatchSettings;
use App\Services\JawalySMSService;
use App\Services\Notifications\AdminAlertSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The team used to learn about a booking, a cancellation or a worker running
 * late only by watching the panel. These pin which events reach them, on
 * which channel, and that an alert can never break the action behind it.
 */
class AdminAlertsTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create(['name' => 'Hani', 'email' => 'hani@example.test', 'password' => 'secret-for-tests']);
        $this->admin->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));

        $this->setting('emails', 'ops@velto.test');
        $this->setting('phones', '966500000009');
        $this->setting('language', 'en');

        $this->customer = Customer::create([
            'name' => 'Sara', 'phone' => '+966500000001', 'status' => 'active',
            'city' => 'Riyadh', 'preferred_language' => 'ar',
        ]);
    }

    private function setting(string $key, string $value): void
    {
        AppSetting::updateOrCreate(['key' => "alerts.$key"], ['group' => 'alerts', 'value' => $value, 'type' => 'string']);
        AdminAlertSettings::flush();
        DispatchSettings::flush();
    }

    private function booking(array $attributes = []): Appointment
    {
        return Appointment::create($attributes + [
            'customer_id' => $this->customer->id,
            'status' => Appointment::STATUS_CONFIRMED,
            'scheduled_at' => now()->addDay(),
            'service_name' => 'Express exterior wash',
            'base_price' => 35, 'addons_total' => 0, 'discount_total' => 0, 'total_price' => 35,
            'payment_method' => 'wallet', 'payment_status' => 'paid',
            'auto_dispatch' => false,
        ]);
    }

    /** @return list<string> */
    private function bellTitles(): array
    {
        return DatabaseNotification::query()->where('notifiable_id', $this->admin->id)->get()
            ->map(fn ($n) => $n->data['title'] ?? '')->all();
    }

    public function test_a_confirmed_booking_rings_the_bell_and_queues_email_and_sms(): void
    {
        Queue::fake([SendAdminAlert::class]);

        $appointment = $this->booking();

        $this->assertContains("New booking #{$appointment->id}", $this->bellTitles());
        Queue::assertPushed(SendAdminAlert::class, fn (SendAdminAlert $job) => $job->title === "New booking #{$appointment->id}"
            && $job->emails === ['ops@velto.test']
            && $job->phones === ['966500000009']);
    }

    public function test_with_auto_assign_off_a_new_booking_also_says_it_needs_a_worker(): void
    {
        AppSetting::updateOrCreate(['key' => 'dispatch.auto_dispatch_enabled'], ['group' => 'dispatch', 'value' => '0', 'type' => 'string']);
        DispatchSettings::flush();
        Queue::fake([SendAdminAlert::class]);

        $appointment = $this->booking();

        $this->assertContains("Order #{$appointment->id} needs a worker", $this->bellTitles());
    }

    public function test_an_abandoned_card_payment_is_not_reported_as_a_cancellation(): void
    {
        Queue::fake([SendAdminAlert::class]);
        $appointment = $this->booking(['status' => Appointment::STATUS_PENDING, 'payment_status' => 'pending']);

        $appointment->update(['status' => Appointment::STATUS_CANCELLED, 'cancelled_at' => now()]);

        $this->assertNotContains("Order #{$appointment->id} cancelled", $this->bellTitles());
        $this->assertNotContains("New booking #{$appointment->id}", $this->bellTitles());
    }

    public function test_the_worker_tracking_steps_reach_the_bell(): void
    {
        Queue::fake([SendAdminAlert::class]);
        $worker = Worker::create(['name' => 'Ahmed', 'phone' => '+966540000001']);
        $appointment = $this->booking(['worker_id' => $worker->id]);
        $this->actingAs($worker, 'worker');

        $appointment->update(['status' => Appointment::STATUS_ON_THE_WAY, 'started_at' => now()]);
        $appointment->update(['status' => Appointment::STATUS_ARRIVED, 'arrived_at' => now()]);
        $appointment->update(['status' => Appointment::STATUS_COMPLETED, 'completed_at' => now()]);

        $titles = $this->bellTitles();
        $this->assertContains("Ahmed is on the way — order #{$appointment->id}", $titles);
        $this->assertContains("Ahmed arrived — order #{$appointment->id}", $titles);
        $this->assertContains("Order #{$appointment->id} completed by Ahmed", $titles);
    }

    public function test_the_admin_who_cancelled_does_not_get_a_bell_for_it(): void
    {
        Queue::fake([SendAdminAlert::class]);
        $appointment = $this->booking();
        $this->actingAs($this->admin);

        $appointment->update(['status' => Appointment::STATUS_CANCELLED, 'cancelled_at' => now()]);

        $this->assertNotContains("Order #{$appointment->id} cancelled", $this->bellTitles());
        // …but the email list still hears about it.
        Queue::assertPushed(SendAdminAlert::class, fn (SendAdminAlert $job) => $job->title === "Order #{$appointment->id} cancelled");
    }

    public function test_a_channel_switched_off_sends_nothing_on_it(): void
    {
        $this->setting('matrix', json_encode(['booking_confirmed' => ['bell' => false, 'email' => false, 'sms' => false]]));
        Queue::fake([SendAdminAlert::class]);

        $appointment = $this->booking();

        $this->assertNotContains("New booking #{$appointment->id}", $this->bellTitles());
        Queue::assertNotPushed(SendAdminAlert::class, fn (SendAdminAlert $job) => str_contains($job->title, 'New booking'));
    }

    public function test_a_new_customer_is_announced(): void
    {
        Queue::fake([SendAdminAlert::class]);

        Customer::create(['name' => 'Nora', 'phone' => '+966500000002', 'status' => 'active', 'city' => 'Riyadh', 'preferred_language' => 'ar']);

        $this->assertContains('New customer: Nora', $this->bellTitles());
    }

    public function test_the_job_emails_and_texts_the_lists(): void
    {
        Mail::fake();
        $sms = $this->mock(JawalySMSService::class);
        $sms->shouldReceive('sendSMS')->once()
            ->withArgs(fn (array $numbers, string $message) => $numbers === ['966500000009'] && str_contains($message, 'New booking #7'))
            ->andReturn(['success' => true]);

        (new SendAdminAlert('New booking #7', 'Sara · Wash', 'https://admin.velto.sa/admin/appointments/7', ['ops@velto.test'], ['966500000009']))
            ->handle($sms);

        Mail::assertSent(\App\Mail\AdminAlertMail::class, fn ($mail) => $mail->hasTo('ops@velto.test') && $mail->title === 'New booking #7');
    }

    public function test_a_broken_alert_never_breaks_the_booking(): void
    {
        // Corrupt settings JSON must not take the booking down with it.
        $this->setting('matrix', '{not json');
        Queue::fake([SendAdminAlert::class]);

        $appointment = $this->booking();

        $this->assertTrue($appointment->exists);
    }
}
