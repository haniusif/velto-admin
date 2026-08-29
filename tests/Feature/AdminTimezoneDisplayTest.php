<?php

namespace Tests\Feature;

use App\Filament\Resources\Appointments\AppointmentResource;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentTimezone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The admin holds two kinds of time in the same database.
 *
 * created_at, completed_at and the rest are true UTC instants, and were being
 * shown to staff three hours behind the clock on the wall. scheduled_at and a
 * slot's date/start_time are Riyadh wall-clock digits stored naively — already
 * correct as written, and ruined by a second conversion: an 18:30 wash would
 * read 21:30.
 *
 * So the panel converts for display, and every naive column opts out. Getting
 * either half wrong is a three-hour error nobody would notice until a
 * specialist turned up at the wrong time.
 */
class AdminTimezoneDisplayTest extends TestCase
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
        foreach (['ViewAny:Appointment', 'View:Appointment'] as $permission) {
            $role->givePermissionTo(Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']));
        }
        $user->assignRole($role);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
    }

    private function booking(): Appointment
    {
        $customer = Customer::create([
            'name' => 'Customer',
            'phone' => '+966500000001',
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
        ]);

        $appointment = Appointment::create([
            'customer_id' => $customer->id,
            'status' => Appointment::STATUS_CONFIRMED,
            // Riyadh wall clock, stored naively — this is 6:30 in the evening.
            'scheduled_at' => '2026-08-29 18:30:00',
            'service_name' => 'Express exterior wash',
            'base_price' => 35,
            'addons_total' => 0,
            'discount_total' => 0,
            'total_price' => 35,
            'payment_method' => 'wallet',
            'payment_status' => 'paid',
        ]);

        // A true UTC instant: 22:00 UTC is 01:00 the next day in Riyadh.
        $appointment->forceFill(['created_at' => '2026-08-29 22:00:00'])->save();

        return $appointment->refresh();
    }

    public function test_the_panel_displays_in_riyadh(): void
    {
        $this->assertSame(config('app.business_timezone'), FilamentTimezone::get());
    }

    public function test_storage_is_left_on_utc(): void
    {
        // The half of this that must not change: flipping app.timezone would
        // write new rows in Riyadh while every existing row stayed UTC, in the
        // same column, with nothing to tell them apart.
        $this->assertSame('UTC', config('app.timezone'));
    }

    public function test_a_booking_time_is_shown_exactly_as_booked(): void
    {
        $html = $this->get(AppointmentResource::getUrl('view', ['record' => $this->booking()]))
            ->assertOk()
            ->getContent();

        $this->assertStringContainsString('Aug 29, 2026 18:30:00', $html, 'the booking time was shifted');
        // 21:30 is what a second conversion would produce.
        $this->assertStringNotContainsString('21:30:00', $html, 'the booking time was converted twice');
    }

    public function test_a_real_instant_is_converted_for_the_reader(): void
    {
        // 22:00 UTC is 01:00 Riyadh the following day.
        $html = $this->get(AppointmentResource::getUrl('view', ['record' => $this->booking()]))
            ->getContent();

        $this->assertStringContainsString('Aug 30, 2026 01:00:00', $html, 'created_at is still being shown in UTC');
        $this->assertStringNotContainsString('Aug 29, 2026 22:00:00', $html, 'created_at is unconverted');
    }

    public function test_every_naive_column_in_the_panel_opts_out(): void
    {
        // A new screen that forgets the pin shows a booking three hours late,
        // which is the failure this whole arrangement exists to prevent.
        $naive = ['scheduled_at', 'start_time', 'end_time'];
        $offenders = [];

        foreach ($this->phpFilesIn(app_path('Filament')) as $file) {
            $contents = file_get_contents($file);

            foreach ($naive as $column) {
                if (! preg_match("/make\('{$column}'\)(.{0,40})/s", $contents, $m)) {
                    continue;
                }

                if (! str_contains($m[1], 'timezone(')) {
                    $offenders[] = basename($file).": {$column}";
                }
            }
        }

        $this->assertSame([], $offenders, 'a wall-clock column is not pinned and will be shifted');
    }

    /** @return iterable<string> */
    private function phpFilesIn(string $directory): iterable
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($directory));

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->getExtension() === 'php') {
                yield $file->getPathname();
            }
        }
    }
}
