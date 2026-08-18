<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\CustomerNotification;
use App\Models\Worker;
use App\Models\WorkerNotification;
use App\Services\Notifications\NotificationDispatcher;
use App\Support\BookingTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

/**
 * Notification bodies are composed here and stored, so the apps show them
 * verbatim. They carried a 24-hour clock ("2026-08-15 00:17") while both apps
 * had moved to 12-hour, which put the same booking on screen twice in two
 * different dialects — the card reading "12:17 ص" above a body reading "00:17".
 */
class NotificationClockFormatTest extends TestCase
{
    use RefreshDatabase;

    private function customer(string $language = 'ar'): Customer
    {
        return Customer::create([
            'name' => 'Test customer',
            'phone' => '+966500000001',
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => $language,
        ]);
    }

    private function worker(): Worker
    {
        return Worker::create([
            'name' => 'Test worker',
            'phone' => '+966500000002',
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
        ]);
    }

    private function appointment(Customer $customer, string $scheduledAt, ?Worker $worker = null): Appointment
    {
        return Appointment::create([
            'customer_id' => $customer->id,
            'worker_id' => $worker?->id,
            'status' => Appointment::STATUS_CONFIRMED,
            // Stored naively: these digits already ARE Riyadh wall clock.
            'scheduled_at' => $scheduledAt,
            'service_name' => 'Express exterior wash',
            'service_name_ar' => 'غسيل خارجي سريع',
            'base_price' => 35,
            'addons_total' => 0,
            'discount_total' => 0,
            'total_price' => 35,
            'payment_method' => 'wallet',
            'payment_status' => 'paid',
        ]);
    }

    private function dispatcher(): NotificationDispatcher
    {
        return app(NotificationDispatcher::class);
    }

    public function test_the_label_is_twelve_hour_in_both_languages(): void
    {
        $at = Carbon::parse('2026-08-15 16:00:00');

        $this->assertSame('2026-08-15 4:00 PM', BookingTime::wallClockLabel($at, arabic: false));
        $this->assertSame('2026-08-15 4:00 م', BookingTime::wallClockLabel($at, arabic: true));
    }

    public function test_midnight_and_noon_are_twelve_not_zero(): void
    {
        // The case from the worker's inbox: 00:17 read as "0:17 AM" would be
        // wrong, and reads as a missing hour rather than just past midnight.
        $this->assertSame(
            '2026-08-15 12:17 AM',
            BookingTime::wallClockLabel(Carbon::parse('2026-08-15 00:17:00'), arabic: false),
        );
        $this->assertSame(
            '2026-08-15 12:17 ص',
            BookingTime::wallClockLabel(Carbon::parse('2026-08-15 00:17:00'), arabic: true),
        );
        $this->assertSame(
            '2026-08-15 12:30 PM',
            BookingTime::wallClockLabel(Carbon::parse('2026-08-15 12:30:00'), arabic: false),
        );
    }

    public function test_the_label_never_shifts_the_clock(): void
    {
        // scheduled_at holds Riyadh wall-clock digits stored naively while the
        // app runs in UTC. Any timezone conversion here would move every
        // notification three hours off the booking it describes.
        $label = BookingTime::wallClockLabel(Carbon::parse('2026-08-15 21:00:00'), arabic: false);

        $this->assertSame('2026-08-15 9:00 PM', $label);
    }

    public function test_a_missing_schedule_yields_null_rather_than_a_wrong_time(): void
    {
        $this->assertNull(BookingTime::wallClockLabel(null, arabic: true));
    }

    public function test_the_worker_assignment_body_reads_twelve_hour(): void
    {
        $worker = $this->worker();
        $appointment = $this->appointment($this->customer(), '2026-08-15 00:17:00', $worker);

        $this->dispatcher()->workerAssigned($appointment);

        $row = WorkerNotification::where('worker_id', $worker->id)->latest('id')->first();

        $this->assertNotNull($row);
        $this->assertStringContainsString('12:17 AM', $row->body);
        $this->assertStringContainsString('12:17 ص', $row->body_ar);
        $this->assertStringNotContainsString('00:17', $row->body);
        $this->assertStringNotContainsString('00:17', $row->body_ar);
    }

    public function test_the_arabic_worker_body_keeps_the_arabic_service_name(): void
    {
        $worker = $this->worker();
        $appointment = $this->appointment($this->customer(), '2026-08-15 16:00:00', $worker);

        $this->dispatcher()->workerAssigned($appointment);

        $row = WorkerNotification::where('worker_id', $worker->id)->latest('id')->first();

        $this->assertStringContainsString('غسيل خارجي سريع', $row->body_ar);
        $this->assertStringContainsString('Express exterior wash', $row->body);
    }

    public function test_the_booking_confirmation_is_twelve_hour_and_fully_arabic(): void
    {
        // The Arabic body was previously built from the English service name,
        // so an Arabic customer's confirmation arrived half in English — the
        // "notifications come in English" complaint, not a translation gap.
        $customer = $this->customer();
        $appointment = $this->appointment($customer, '2026-08-19 18:30:00');

        $this->dispatcher()->customerBooked($appointment);

        $row = CustomerNotification::where('customer_id', $customer->id)->latest('id')->first();

        $this->assertNotNull($row);
        $this->assertStringContainsString('6:30 PM', $row->body);
        $this->assertStringContainsString('6:30 م', $row->body_ar);
        $this->assertStringContainsString('غسيل خارجي سريع', $row->body_ar);
        $this->assertStringNotContainsString('Express exterior wash', $row->body_ar);
        $this->assertStringNotContainsString('18:30', $row->body_ar);
    }

    public function test_a_cancellation_body_is_twelve_hour_too(): void
    {
        $worker = $this->worker();
        $appointment = $this->appointment($this->customer(), '2026-08-19 21:00:00', $worker);

        $this->dispatcher()->workerJobCancelled($appointment);

        $row = WorkerNotification::where('worker_id', $worker->id)->latest('id')->first();

        $this->assertStringContainsString('9:00 PM', $row->body);
        $this->assertStringContainsString('9:00 م', $row->body_ar);
        $this->assertStringNotContainsString('21:00', $row->body_ar);
    }
}
