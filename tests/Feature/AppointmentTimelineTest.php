<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\DispatchEvent;
use App\Models\PaymentTransaction;
use App\Models\Worker;
use App\Support\AppointmentTimeline;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * "Why was this job late?" used to mean opening the order, its payments and
 * the dispatch log side by side and lining the times up by hand. The order
 * page now draws them on one rail; these pin what goes on it and in what order.
 */
class AppointmentTimelineTest extends TestCase
{
    use RefreshDatabase;

    private function booking(array $attributes = []): Appointment
    {
        $customer = Customer::create([
            'name' => 'Sara',
            'phone' => '+966500000001',
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
        ]);

        $appointment = Appointment::create($attributes + [
            'customer_id' => $customer->id,
            'status' => Appointment::STATUS_COMPLETED,
            'scheduled_at' => '2026-08-29 18:30:00',
            'service_name' => 'Express exterior wash',
            'base_price' => 35,
            'addons_total' => 0,
            'discount_total' => 0,
            'total_price' => 35,
            'payment_method' => 'card',
            'payment_status' => 'paid',
        ]);
        $appointment->forceFill(['created_at' => '2026-08-29 10:00:00'])->save();

        return $appointment->refresh();
    }

    public function test_every_source_lands_on_the_rail_in_time_order(): void
    {
        $worker = Worker::create(['name' => 'Ahmed', 'phone' => '+966540000001']);
        $appointment = $this->booking([
            'worker_id' => $worker->id,
            'started_at' => '2026-08-29 18:05:00',
            'completed_at' => '2026-08-29 19:10:00',
        ]);

        PaymentTransaction::create([
            'customer_id' => $appointment->customer_id,
            'appointment_id' => $appointment->id,
            'gateway' => 'arb',
            'track_id' => 'T-1',
            'status' => PaymentTransaction::STATUS_CAPTURED,
            'amount' => 35,
            'currency' => 'SAR',
        ])->forceFill(['created_at' => '2026-08-29 10:01:00'])->save();

        DispatchEvent::create([
            'appointment_id' => $appointment->id, 'worker_id' => $worker->id,
            'type' => DispatchEvent::TYPE_ASSIGNED, 'actor' => 'engine',
            'created_at' => Carbon::parse('2026-08-29 17:00:00'),
        ]);

        $titles = AppointmentTimeline::for($appointment)->pluck('title')->all();

        $this->assertSame([
            __('Booked'),
            __('Payment captured'),
            __('Worker assigned'),
            __('On the way'),
            __('Scheduled time'),
            __('Completed'),
        ], $titles);
    }

    public function test_a_dispatch_event_names_the_worker_it_concerned(): void
    {
        $worker = Worker::create(['name' => 'Khalid', 'phone' => '+966540000002']);
        $appointment = $this->booking();

        DispatchEvent::create([
            'appointment_id' => $appointment->id, 'worker_id' => $worker->id,
            'type' => DispatchEvent::TYPE_REJECTED, 'actor' => 'worker', 'reason' => 'too_far',
            'created_at' => Carbon::parse('2026-08-29 11:00:00'),
        ]);

        $declined = AppointmentTimeline::for($appointment)->firstWhere('title', __('Worker declined'));

        $this->assertNotNull($declined);
        $this->assertStringContainsString('Khalid', $declined['detail']);
    }

    public function test_an_open_booking_marks_its_slot_as_still_to_come(): void
    {
        Carbon::setTestNow('2026-08-29 12:00:00');

        $appointment = $this->booking(['status' => Appointment::STATUS_CONFIRMED]);
        $slot = AppointmentTimeline::for($appointment)->firstWhere('title', __('Scheduled time'));

        $this->assertTrue($slot['planned']);
        $this->assertSame(__('Upcoming'), $slot['detail']);
    }

    public function test_steps_a_booking_never_reached_are_left_off(): void
    {
        $appointment = $this->booking(['status' => Appointment::STATUS_CANCELLED, 'cancelled_at' => '2026-08-29 11:00:00']);
        $titles = AppointmentTimeline::for($appointment)->pluck('title');

        $this->assertContains(__('Cancelled'), $titles);
        $this->assertNotContains(__('Arrived'), $titles);
        $this->assertNotContains(__('Completed'), $titles);
    }
}
