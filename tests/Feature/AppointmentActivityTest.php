<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\AppointmentActivity;
use App\Models\Customer;
use App\Models\User;
use App\Models\Worker;
use App\Support\AppointmentTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * "Who cancelled this?" had no answer: the order row kept only its latest
 * state. Every change now leaves a row naming who made it, and the order's
 * timeline shows it.
 */
class AppointmentActivityTest extends TestCase
{
    use RefreshDatabase;

    private Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::create([
            'name' => 'Sara',
            'phone' => '+966500000001',
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
        ]);
    }

    private function booking(array $attributes = []): Appointment
    {
        return Appointment::create($attributes + [
            'customer_id' => $this->customer->id,
            'status' => Appointment::STATUS_CONFIRMED,
            'scheduled_at' => '2026-08-29 18:30:00',
            'service_name' => 'Express exterior wash',
            'base_price' => 35,
            'addons_total' => 0,
            'discount_total' => 0,
            'total_price' => 35,
            'payment_method' => 'wallet',
            'payment_status' => 'paid',
            'auto_dispatch' => false,
        ]);
    }

    private function lastActivity(Appointment $appointment): AppointmentActivity
    {
        return AppointmentActivity::where('appointment_id', $appointment->id)->latest('id')->firstOrFail();
    }

    public function test_a_booking_the_customer_made_is_logged_as_theirs(): void
    {
        $this->actingAs($this->customer, 'customer');

        $activity = $this->lastActivity($this->booking());

        $this->assertSame(AppointmentActivity::EVENT_CREATED, $activity->event);
        $this->assertSame(AppointmentActivity::ACTOR_CUSTOMER, $activity->actor_type);
        $this->assertSame('Sara', $activity->actor_name);
    }

    public function test_an_admin_cancellation_names_the_admin_and_the_status_move(): void
    {
        $appointment = $this->booking();
        $admin = User::create(['name' => 'Hani', 'email' => 'hani@example.test', 'password' => 'secret-for-tests']);
        $this->actingAs($admin);

        $appointment->update(['status' => Appointment::STATUS_CANCELLED, 'cancelled_at' => now()]);

        $activity = $this->lastActivity($appointment);
        $this->assertSame(AppointmentActivity::EVENT_STATUS_CHANGED, $activity->event);
        $this->assertSame(AppointmentActivity::ACTOR_ADMIN, $activity->actor_type);
        $this->assertSame('Hani', $activity->actor_name);
        $this->assertSame([Appointment::STATUS_CONFIRMED, Appointment::STATUS_CANCELLED], $activity->changes['status']);
        // The stamp rides along with the status; it is not news of its own.
        $this->assertArrayNotHasKey('cancelled_at', $activity->changes);
    }

    public function test_a_worker_moving_the_job_along_is_logged_as_the_worker(): void
    {
        $worker = Worker::create(['name' => 'Ahmed', 'phone' => '+966540000001']);
        $appointment = $this->booking(['worker_id' => $worker->id]);
        $this->actingAs($worker, 'worker');

        $appointment->update(['status' => Appointment::STATUS_ON_THE_WAY, 'started_at' => now()]);

        $activity = $this->lastActivity($appointment);
        $this->assertSame(AppointmentActivity::ACTOR_WORKER, $activity->actor_type);
        $this->assertSame('Ahmed', $activity->actor_name);
    }

    public function test_bookkeeping_that_changes_nothing_a_person_cares_about_is_not_logged(): void
    {
        $appointment = $this->booking();
        $before = AppointmentActivity::count();

        $appointment->update(['dispatch_attempts' => 3, 'dispatch_state' => 'waiting']);

        $this->assertSame($before, AppointmentActivity::count());
    }

    public function test_the_stale_booking_sweep_leaves_a_trail(): void
    {
        // Was a bulk UPDATE, which bypasses model events entirely.
        $appointment = $this->booking(['status' => Appointment::STATUS_PENDING, 'payment_status' => 'pending']);
        $appointment->forceFill(['created_at' => now()->subHours(3)])->saveQuietly();

        $this->artisan('bookings:cancel-stale', ['--minutes' => 30])->assertSuccessful();

        $activity = $this->lastActivity($appointment);
        $this->assertSame(Appointment::STATUS_CANCELLED, $appointment->fresh()->status);
        $this->assertSame(AppointmentActivity::ACTOR_SYSTEM, $activity->actor_type);
        $this->assertSame([Appointment::STATUS_PENDING, Appointment::STATUS_CANCELLED], $activity->changes['status']);
    }

    public function test_the_timeline_says_who_did_it_and_what_changed(): void
    {
        $appointment = $this->booking();
        $admin = User::create(['name' => 'Hani', 'email' => 'hani@example.test', 'password' => 'secret-for-tests']);
        $this->actingAs($admin);

        $appointment->update(['scheduled_at' => '2026-08-30 09:00:00']);
        // A separate action, a little later — not one save split in two.
        $this->travel(2)->minutes();
        $appointment->update(['status' => Appointment::STATUS_CANCELLED, 'cancelled_at' => now()]);

        $timeline = AppointmentTimeline::for($appointment->fresh());

        $edit = $timeline->firstWhere('title', __('Order edited'));
        $this->assertStringContainsString('Hani', $edit['actor']);
        $this->assertSame(__('Scheduled at'), $edit['changes'][0][0]);

        $cancelled = $timeline->where('title', __('Cancelled'));
        $this->assertCount(1, $cancelled, 'the log entry and the bare stamp are the same moment');
        $this->assertStringContainsString('Hani', $cancelled->first()['actor']);
    }

    public function test_one_action_that_saves_twice_reads_as_one_step(): void
    {
        // The admin cancel sets the status, then marks the refund.
        $appointment = $this->booking();
        $admin = User::create(['name' => 'Hani', 'email' => 'hani@example.test', 'password' => 'secret-for-tests']);
        $this->actingAs($admin);

        $appointment->update(['status' => Appointment::STATUS_CANCELLED, 'cancelled_at' => now()]);
        $appointment->update(['payment_status' => 'refunded']);

        $timeline = AppointmentTimeline::for($appointment->fresh());

        $this->assertNull($timeline->firstWhere('title', __('Order edited')));
        $this->assertSame(__('Payment status'), $timeline->firstWhere('title', __('Cancelled'))['changes'][0][0]);
    }
}
