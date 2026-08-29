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
 * The app, the database connection and the panel all now name Asia/Riyadh, so
 * a stored value means what it says and is displayed unchanged.
 *
 * That agreement is the whole safety property. If app.timezone and the
 * connection zone ever drift apart, MySQL converts TIMESTAMP columns with one
 * zone while PHP reads them in another and every timestamp lands three hours
 * out — the kind of error nobody notices until a specialist turns up at the
 * wrong time.
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

        // Written by the app, so already Riyadh now that app.timezone is.
        $appointment->forceFill(['created_at' => '2026-08-29 22:00:00'])->save();

        return $appointment->refresh();
    }

    public function test_the_panel_displays_in_riyadh(): void
    {
        $this->assertSame(config('app.business_timezone'), FilamentTimezone::get());
    }

    public function test_the_application_and_the_database_name_the_same_zone(): void
    {
        // The app now runs on Riyadh. That is only safe because the connection
        // is pinned to the matching offset: MySQL converts TIMESTAMP columns
        // with the session zone, so if these two ever drift apart every read
        // comes back three hours from what was written.
        $this->assertSame('Asia/Riyadh', config('app.timezone'));
        $this->assertSame('+03:00', config('database.connections.mysql.timezone'));
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

    public function test_a_timestamp_is_shown_in_the_zone_it_was_written_in(): void
    {
        // Now that the app writes Riyadh, a stored 22:00 means 22:00 here and
        // is displayed unchanged. Anything else would mean the panel is
        // converting a value that needs no conversion — the same double-shift
        // the wall-clock pins exist to prevent, just on a different column.
        $html = $this->get(AppointmentResource::getUrl('view', ['record' => $this->booking()]))
            ->getContent();

        $this->assertStringContainsString('Aug 29, 2026 22:00:00', $html, 'created_at was shifted');
        $this->assertStringNotContainsString('Aug 30, 2026 01:00:00', $html, 'created_at was converted twice');
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
