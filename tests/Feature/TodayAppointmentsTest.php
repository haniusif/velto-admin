<?php

namespace Tests\Feature;

use App\Filament\Pages\TodayAppointments;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use App\Models\Worker;
use App\Support\BookingTime;
use Carbon\Carbon;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The Dispatch Board answers "what state is each job in". Nothing answered
 * "what is happening today, and when" — the question asked at the start of a
 * shift and every time the phone rings — without filtering the appointments
 * list by hand.
 */
class TodayAppointmentsTest extends TestCase
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
        $user->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
    }

    private function customer(): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create([
            'name' => "Customer {$n}",
            'phone' => '+96650000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
        ]);
    }

    private function booking(string $scheduledAt, string $status = Appointment::STATUS_CONFIRMED, ?Worker $worker = null): Appointment
    {
        return Appointment::create([
            'customer_id' => $this->customer()->id,
            'worker_id' => $worker?->id,
            'status' => $status,
            'scheduled_at' => $scheduledAt,
            'service_name' => 'Express exterior wash',
            'base_price' => 40,
            'addons_total' => 0,
            'discount_total' => 0,
            'total_price' => 40,
            'payment_method' => 'wallet',
            'payment_status' => 'paid',
        ]);
    }

    /** Riyadh wall clock, which is what scheduled_at is written in. */
    private function riyadhToday(): string
    {
        return BookingTime::nowWallClock()->toDateString();
    }

    public function test_the_page_loads(): void
    {
        $this->get(TodayAppointments::getUrl())->assertOk();
    }

    public function test_it_shows_todays_bookings_and_only_those(): void
    {
        $today = $this->riyadhToday();
        $this->booking("{$today} 16:00:00");
        $this->booking(BookingTime::nowWallClock()->addDay()->toDateString().' 16:00:00');
        $this->booking(BookingTime::nowWallClock()->subDay()->toDateString().' 16:00:00');

        $this->assertSame(1, TodayAppointments::todaysBookings()->count());
    }

    public function test_today_is_measured_in_riyadh_not_utc(): void
    {
        // scheduled_at holds Riyadh wall-clock digits stored naively. Taking
        // the day from UTC rolls it over at 03:00 local, so anyone working
        // late would be handed tomorrow's sheet.
        $this->assertSame(
            BookingTime::nowWallClock()->toDateString(),
            Carbon::now(config('app.business_timezone'))->toDateString(),
        );
    }

    public function test_the_summary_counts_each_state(): void
    {
        $today = $this->riyadhToday();
        $worker = Worker::create([
            'name' => 'Worker',
            'phone' => '+966590000001',
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
        ]);

        $this->booking("{$today} 09:00:00", Appointment::STATUS_COMPLETED, $worker);
        $this->booking("{$today} 11:00:00", Appointment::STATUS_COMPLETED, $worker);
        $this->booking("{$today} 13:00:00", Appointment::STATUS_CONFIRMED, $worker);
        $this->booking("{$today} 15:00:00", Appointment::STATUS_CONFIRMED);       // unassigned
        $this->booking("{$today} 17:00:00", Appointment::STATUS_IN_PROGRESS, $worker);
        $this->booking("{$today} 19:00:00", Appointment::STATUS_CANCELLED);

        $summary = (new TodayAppointments)->summary();

        $this->assertSame(6, $summary['total']);
        $this->assertSame(2, $summary['upcoming']);
        $this->assertSame(1, $summary['in_progress']);
        $this->assertSame(2, $summary['completed']);
        $this->assertSame(1, $summary['cancelled']);
        $this->assertSame(80.0, $summary['revenue'], 'only completed visits are earnings');
    }

    public function test_a_cancelled_booking_is_not_counted_as_unassigned(): void
    {
        // Unassigned is the tile that means somebody must start phoning
        // workers. A cancelled booking needs nobody, and counting it would
        // send someone chasing a job that is not happening.
        $today = $this->riyadhToday();
        $this->booking("{$today} 15:00:00", Appointment::STATUS_CANCELLED);

        $this->assertSame(0, (new TodayAppointments)->summary()['unassigned']);
    }

    public function test_an_unassigned_live_booking_is_counted(): void
    {
        $this->booking($this->riyadhToday().' 15:00:00', Appointment::STATUS_CONFIRMED);

        $this->assertSame(1, (new TodayAppointments)->summary()['unassigned']);
    }

    public function test_the_navigation_badge_counts_today_and_hides_when_empty(): void
    {
        $this->assertNull(TodayAppointments::getNavigationBadge(), 'an empty day should show no badge');

        $this->booking($this->riyadhToday().' 16:00:00');

        $this->assertSame('1', TodayAppointments::getNavigationBadge());
    }

    public function test_the_page_renders_a_booking_with_its_time_and_customer(): void
    {
        $today = $this->riyadhToday();
        $this->booking("{$today} 16:00:00");

        $this->get(TodayAppointments::getUrl())
            ->assertOk()
            ->assertSee('4:00 PM')          // 12-hour, matching both apps
            ->assertSee('Express exterior wash');
    }

    public function test_the_earnings_line_renders_in_riyals(): void
    {
        // This line only appears once something is completed, so without a
        // completed booking the money helper it calls is never exercised.
        $this->booking($this->riyadhToday().' 09:00:00', Appointment::STATUS_COMPLETED);

        $html = $this->get(TodayAppointments::getUrl())->assertOk()->getContent();

        $this->assertStringContainsString(__('Earned today'), $html);
        $this->assertTrue(
            str_contains($html, 'SAR') || str_contains($html, 'ر.س'),
            'the day\'s earnings are not labelled as riyals',
        );
        $this->assertStringNotContainsString('$40', $html);
    }

    public function test_an_empty_day_still_renders(): void
    {
        $this->get(TodayAppointments::getUrl())
            ->assertOk()
            ->assertSee(__('Nothing booked for today'));
    }
}
