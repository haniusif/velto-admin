<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\TimeSlot;
use App\Models\Vehicle;
use App\Models\WashPackage;
use App\Support\BookingTime;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Booking was allowed right up to the second a slot began: a customer could
 * take an 18:30 wash at 18:29, leaving no time to get a specialist there. A
 * minimum lead time now applies, and it has to apply in all three places —
 * the availability feed, booking, and rescheduling — or the app offers a time
 * it then refuses to accept.
 */
class BookingLeadTimeTest extends TestCase
{
    use RefreshDatabase;

    private function riyadhNow(): CarbonImmutable
    {
        return CarbonImmutable::now(config('app.business_timezone'));
    }

    private function slotIn(int $minutes, int $capacity = 5): TimeSlot
    {
        $moment = $this->riyadhNow()->addMinutes($minutes);

        return TimeSlot::create([
            'date' => $moment->toDateString(),
            'start_time' => $moment->format('H:i:s'),
            'end_time' => $moment->addHour()->format('H:i:s'),
            'capacity' => $capacity,
            'booked_count' => 0,
            'is_active' => true,
        ]);
    }

    /** The migration seeds this row, so tests change it rather than add it. */
    private function setLeadMinutes(string $minutes): void
    {
        AppSetting::updateOrCreate(
            ['key' => 'booking.min_lead_minutes'],
            ['group' => 'booking', 'label' => 'lead', 'value' => $minutes, 'type' => 'number'],
        );
    }

    private function isBookable(TimeSlot $slot): bool
    {
        return BookingTime::isBookable($slot->date->toDateString(), $slot->start_time);
    }

    public function test_the_default_lead_time_is_half_an_hour(): void
    {
        $this->assertSame(30, BookingTime::minimumLeadMinutes());
    }

    public function test_a_slot_further_out_than_the_lead_time_is_bookable(): void
    {
        $this->assertTrue($this->isBookable($this->slotIn(31)));
        $this->assertTrue($this->isBookable($this->slotIn(120)));
    }

    public function test_a_slot_inside_the_lead_time_is_refused(): void
    {
        // The case that prompted this: close enough to be useless.
        $this->assertFalse($this->isBookable($this->slotIn(29)));
        $this->assertFalse($this->isBookable($this->slotIn(1)));
    }

    public function test_a_slot_that_already_started_is_still_refused(): void
    {
        // The old rule has to survive the new one.
        $this->assertFalse($this->isBookable($this->slotIn(-1)));
        $this->assertFalse($this->isBookable($this->slotIn(-180)));
    }

    public function test_the_availability_feed_hides_what_booking_would_refuse(): void
    {
        // If these two disagreed, the picker would show a time and the booking
        // endpoint would reject it — which reads as a broken app.
        $tooSoon = $this->slotIn(20);
        $bookable = $this->slotIn(45);

        $ids = collect($this->getJson('/api/v1/catalog/availability')->json('data'))->pluck('id');

        $this->assertNotContains($tooSoon->id, $ids, 'a slot inside the lead time is still being offered');
        $this->assertContains($bookable->id, $ids, 'a bookable slot has gone missing');
    }

    public function test_the_window_can_be_changed_from_the_admin(): void
    {
        // The point of a setting rather than a constant: widen it on a busy
        // day without a deploy.
        $this->setLeadMinutes('120');

        $this->assertSame(120, BookingTime::minimumLeadMinutes());
        $this->assertFalse($this->isBookable($this->slotIn(90)));
        $this->assertTrue($this->isBookable($this->slotIn(150)));
    }

    public function test_a_zero_setting_falls_back_to_refusing_only_started_slots(): void
    {
        $this->setLeadMinutes('0');

        $this->assertTrue($this->isBookable($this->slotIn(1)), 'zero lead time should allow any future slot');
        $this->assertFalse($this->isBookable($this->slotIn(-1)));
    }

    public function test_a_negative_setting_cannot_open_the_past(): void
    {
        // A typo in the admin must not start accepting bookings for slots that
        // have already begun.
        $this->setLeadMinutes('-60');

        $this->assertSame(0, BookingTime::minimumLeadMinutes());
        $this->assertFalse($this->isBookable($this->slotIn(-1)));
    }

    public function test_the_refusal_explains_itself_in_the_customers_language(): void
    {
        // The app shows the API's message verbatim, and nothing sets a
        // per-request locale — so an Arabic customer would otherwise be told
        // in English why the booking failed.
        $this->assertStringContainsString('30', BookingTime::leadTimeMessage(arabic: true));
        $this->assertStringContainsString('دقيقة', BookingTime::leadTimeMessage(arabic: true));
        $this->assertStringContainsString('30 minutes', BookingTime::leadTimeMessage(arabic: false));

        // "In the past" would be untrue of a slot forty minutes away.
        $this->assertStringNotContainsString('past', BookingTime::leadTimeMessage(arabic: false));
    }

    public function test_the_booking_endpoint_refuses_a_slot_inside_the_window(): void
    {
        $customer = Customer::create([
            'name' => 'Customer',
            'phone' => '+966500000001',
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
            'wallet_balance' => 500,
        ]);

        $package = WashPackage::create([
            'name' => 'Express exterior wash',
            'name_ar' => 'غسيل خارجي سريع',
            'type' => 'single',
            'price' => 35,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'name' => 'My car',
            'brand' => 'Toyota',
            'model' => 'Camry',
            'plate' => 'ABC 1234',
            'is_default' => true,
        ]);

        $response = $this->actingAs($customer, 'customer')->postJson('/api/v1/me/appointments', [
            'wash_package_id' => $package->id,
            'vehicle_id' => $vehicle->id,
            'time_slot_id' => $this->slotIn(10)->id,
            'payment_method' => 'wallet',
        ]);

        $response->assertStatus(422);
        $this->assertArrayHasKey('time_slot_id', $response->json('errors'),
            'the endpoint refused for some other reason: '.json_encode($response->json('errors')));
        // Arabic customer, Arabic explanation.
        $this->assertStringContainsString('دقيقة', $response->json('errors.time_slot_id.0'));
    }

    public function test_the_same_endpoint_accepts_a_slot_outside_the_window(): void
    {
        // Guards the over-correction: a lead time that refuses everything is
        // not a lead time, it is an outage.
        $customer = Customer::create([
            'name' => 'Customer',
            'phone' => '+966500000002',
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
            'wallet_balance' => 500,
        ]);
        $package = WashPackage::create([
            'name' => 'Express exterior wash',
            'name_ar' => 'غسيل خارجي سريع',
            'type' => 'single',
            'price' => 35,
            'duration_minutes' => 30,
            'is_active' => true,
        ]);
        $vehicle = Vehicle::create([
            'customer_id' => $customer->id,
            'name' => 'My car',
            'brand' => 'Toyota',
            'model' => 'Camry',
            'plate' => 'XYZ 9876',
            'is_default' => true,
        ]);

        $this->actingAs($customer, 'customer')->postJson('/api/v1/me/appointments', [
            'wash_package_id' => $package->id,
            'vehicle_id' => $vehicle->id,
            'time_slot_id' => $this->slotIn(90)->id,
            'payment_method' => 'wallet',
        ])->assertCreated();
    }
}
