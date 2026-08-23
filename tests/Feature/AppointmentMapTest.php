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

        // The test environment carries no real key; the map only renders when
        // one is configured, so set a stand-in rather than assert on absence.
        config(['services.google_maps.key' => 'test-maps-key']);
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
            // The live map is built in the browser, so assert on what the
            // page must ship for that to be possible.
            ->assertSee('maps.googleapis.com/maps/api/js', escape: false)
            ->assertSee('x-ref="canvas"', escape: false)
            ->assertSee('google.maps.Map', escape: false)
            // …and on the picture that stands in when it does not come up.
            ->assertSee('maps.googleapis.com/maps/api/staticmap', escape: false)
            ->assertSee('center=24.8112%2C46.6103', escape: false)
            ->assertSee('markers=color%3A0x8863E5%7C24.8112%2C46.6103', escape: false);
    }

    public function test_the_static_picture_survives_the_live_map_failing(): void
    {
        // gm_authFailure is what Google calls on a rejected key, sometimes
        // after the map object exists. Without it the page would sit on a grey
        // rectangle whose only explanation is in the browser console.
        $this->openPage($this->booking(24.8112, 46.6103))
            ->assertSee('gm_authFailure', escape: false)
            ->assertSee('live = false', escape: false);
    }

    public function test_the_loader_url_is_not_mangled_by_html_entity_decoding(): void
    {
        // The script URL is assembled inside an HTML attribute, so a literal
        // ampersand before 'region' decodes to ® and the loader ends up
        // requesting a corrupt URL. Caught exactly that in review.
        $html = $this->openPage($this->booking(24.8112, 46.6103))->getContent();

        // Scope to this component's attribute — the page carries several
        // other x-data blocks, one of which legitimately holds a URL.
        $start = strpos($html, 'live: false');
        $attribute = substr($html, $start, strpos($html, 'x-init="boot()"', $start) - $start);

        $this->assertStringNotContainsString('&', html_entity_decode($attribute, ENT_QUOTES),
            'a raw ampersand in the Alpine attribute will be entity-decoded');
        $this->assertStringNotContainsString('®', html_entity_decode($attribute, ENT_QUOTES));
        $this->assertStringContainsString('URLSearchParams', $attribute);
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
            ->assertDontSee('staticmap', escape: false)
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
            ->assertDontSee('staticmap', escape: false)
            ->assertDontSee('maps/dir/?api=1', escape: false);
    }

    public function test_without_a_key_it_says_so_instead_of_a_broken_image(): void
    {
        // An <img> pointing at an unauthorised URL renders as a torn-image
        // icon, which reads as a bug rather than as missing configuration.
        config(['services.google_maps.key' => null]);

        $this->openPage($this->booking(24.8112, 46.6103))
            ->assertOk()
            ->assertSee(__('Map could not be loaded'))
            ->assertDontSee('staticmap', escape: false)
            // The links still work without a key — they need no API at all.
            ->assertSee('google.com/maps/dir/?api=1&amp;destination=24.8112,46.6103', escape: false);
    }

    public function test_a_static_image_that_will_not_load_is_caught(): void
    {
        // Static Maps is a separately-enabled API: production's key answers
        // 403 for it today. Without an error handler the fallback for a failed
        // live map is itself a broken image.
        $this->openPage($this->booking(24.8112, 46.6103))
            ->assertSee('x-on:error="pictureFailed = true"', escape: false)
            ->assertSee(__('Map could not be loaded'));
    }

    public function test_a_latitude_without_a_longitude_is_not_half_a_map(): void
    {
        $booking = $this->booking(24.8112, null);

        $this->openPage($booking)
            ->assertOk()
            ->assertSee(__('No location recorded for this booking'))
            ->assertDontSee('staticmap', escape: false)
            ->assertDontSee('maps/dir/?api=1', escape: false);
    }
}
