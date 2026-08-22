<?php

namespace Tests\Feature;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The booking page printed latitude and longitude as two numbers, which answer
 * "where is this?" only once you have pasted them somewhere else. Dispatch has
 * to know whether a job is round the corner or across Riyadh before assigning
 * it, so the point is now drawn.
 */
class AppointmentMapTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-for-tests',
        ]);

        $role = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        foreach (['ViewAny:Appointment', 'View:Appointment', 'Update:Appointment'] as $permission) {
            $role->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        }
        $user->assignRole($role);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
    }

    private function booking(?float $lat, ?float $lng, ?string $address = 'حي الملقا'): Appointment
    {
        static $n = 0;
        $n++;

        $customer = Customer::create([
            'name' => "Customer {$n}",
            'phone' => '+96650000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
        ]);

        return Appointment::create([
            'customer_id' => $customer->id,
            'status' => Appointment::STATUS_CONFIRMED,
            'scheduled_at' => '2026-08-22 18:30:00',
            'address_label' => $address,
            'latitude' => $lat,
            'longitude' => $lng,
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

    private function openPage(Appointment $appointment)
    {
        return $this->get(AppointmentResource::getUrl('view', ['record' => $appointment]));
    }

    public function test_a_booking_with_coordinates_renders_a_map_at_that_point(): void
    {
        $booking = $this->booking(24.8112, 46.6103);

        $this->openPage($booking)
            ->assertOk()
            // The map's own coordinates arrive over Livewire rather than in
            // the markup, so assert on the container the visible() rule gates.
            ->assertSee('x-ref="map"', escape: false)
            ->assertSee('mapPicker(', escape: false);
    }

    public function test_the_map_offers_a_way_to_actually_drive_there(): void
    {
        // Coordinates on screen are only useful if they can be handed to a
        // phone; a map with no way out is a picture.
        $booking = $this->booking(24.8112, 46.6103);

        $this->openPage($booking)
            // The href is HTML-escaped in the markup, so match the escaped form.
            ->assertSee('google.com/maps/search/?api=1&amp;query=24.8112,46.6103', escape: false)
            ->assertSee('google.com/maps/dir/?api=1&amp;destination=24.8112,46.6103', escape: false);
    }

    public function test_a_booking_without_coordinates_says_so(): void
    {
        // Bookings taken before the app captured a pin, and admin-created
        // ones, have none. An empty grey box would read as a broken map.
        $booking = $this->booking(null, null);

        $this->openPage($booking)
            ->assertOk()
            ->assertSee(__('No location recorded for this booking'))
            ->assertDontSee('x-ref="map"', escape: false)
            ->assertDontSee('maps/dir/?api=1', escape: false);
    }

    public function test_the_fallback_still_shows_whatever_address_was_written(): void
    {
        $booking = $this->booking(null, null, 'حي النرجس، الرياض');

        $this->openPage($booking)->assertSee('حي النرجس، الرياض');
    }

    public function test_a_zero_point_is_treated_as_missing_not_as_the_atlantic(): void
    {
        // 0,0 is the Gulf of Guinea. Drawing a marker there would look like a
        // real answer rather than absent data.
        $booking = $this->booking(0.0, 0.0);

        $this->openPage($booking)
            ->assertOk()
            ->assertSee(__('No location recorded for this booking'))
            ->assertDontSee('x-ref="map"', escape: false)
            ->assertDontSee('maps/dir/?api=1', escape: false);
    }

    public function test_a_latitude_without_a_longitude_is_not_half_a_map(): void
    {
        $booking = $this->booking(24.8112, null);

        $this->openPage($booking)
            ->assertOk()
            ->assertSee(__('No location recorded for this booking'))
            ->assertDontSee('x-ref="map"', escape: false)
            ->assertDontSee('maps/dir/?api=1', escape: false);
    }
}
