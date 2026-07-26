<?php

namespace Tests\Unit;

use App\Models\Appointment;
use Tests\TestCase;

/**
 * The cancellation cutoff: a customer may only cancel while the booking is
 * still more than Appointment::CANCELLATION_CUTOFF_HOURS away.
 */
class AppointmentCancellationTest extends TestCase
{
    private function booking(string $status, ?\DateTimeInterface $at): Appointment
    {
        $a = new Appointment;
        $a->status = $status;
        $a->scheduled_at = $at;

        return $a;
    }

    public function test_can_cancel_well_before_the_cutoff(): void
    {
        $a = $this->booking(Appointment::STATUS_CONFIRMED, now()->addDay());

        $this->assertTrue($a->canCancel());
    }

    public function test_cannot_cancel_inside_the_cutoff(): void
    {
        $a = $this->booking(
            Appointment::STATUS_CONFIRMED,
            now()->addHours(Appointment::CANCELLATION_CUTOFF_HOURS)->subMinute(),
        );

        $this->assertFalse($a->canCancel());
    }

    public function test_can_cancel_just_outside_the_cutoff(): void
    {
        $a = $this->booking(
            Appointment::STATUS_CONFIRMED,
            now()->addHours(Appointment::CANCELLATION_CUTOFF_HOURS)->addMinute(),
        );

        $this->assertTrue($a->canCancel());
    }

    public function test_cannot_cancel_a_booking_in_the_past(): void
    {
        $a = $this->booking(Appointment::STATUS_CONFIRMED, now()->subHour());

        $this->assertFalse($a->canCancel());
    }

    public function test_cannot_cancel_an_already_cancelled_booking(): void
    {
        $a = $this->booking(Appointment::STATUS_CANCELLED, now()->addDay());

        $this->assertFalse($a->canCancel());
    }

    public function test_cannot_cancel_a_completed_booking(): void
    {
        $a = $this->booking(Appointment::STATUS_COMPLETED, now()->addDay());

        $this->assertFalse($a->canCancel());
    }

    public function test_cannot_cancel_without_a_scheduled_time(): void
    {
        $a = $this->booking(Appointment::STATUS_PENDING, null);

        $this->assertFalse($a->canCancel());
    }

    /**
     * Rescheduling deliberately keeps the looser "any time in the future" rule,
     * so the cutoff must not leak into isActionable().
     */
    public function test_reschedule_window_is_unaffected_by_the_cancel_cutoff(): void
    {
        $a = $this->booking(Appointment::STATUS_CONFIRMED, now()->addHour());

        $this->assertTrue($a->isActionable());
        $this->assertFalse($a->canCancel());
    }
}
