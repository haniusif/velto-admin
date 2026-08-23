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
 * Filament's built-in default currency is USD, and two entries on the booking
 * page called ->money() without naming one. A 35 SAR wash therefore displayed
 * as $35 — not obviously broken, just wrong by a factor of 3.75, which is the
 * worst kind of wrong on a page someone uses to settle a refund.
 */
class AdminCurrencyTest extends TestCase
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

        return Appointment::create([
            'customer_id' => $customer->id,
            'status' => Appointment::STATUS_CONFIRMED,
            'scheduled_at' => '2026-08-23 17:20:00',
            'service_name' => 'Express exterior wash',
            'base_price' => 35,
            'addons_total' => 0,
            'discount_total' => 5,
            'total_price' => 30,
            'payment_method' => 'apple_pay',
            'payment_status' => 'paid',
        ]);
    }

    public function test_a_booking_shows_no_dollar_amounts(): void
    {
        $html = $this->get(AppointmentResource::getUrl('view', ['record' => $this->booking()]))
            ->assertOk()
            ->getContent();

        // base_price and total_price are the two entries that named no
        // currency, so they are exactly what a USD default would have hit.
        $this->assertStringNotContainsString('$35', $html, 'the base price is still in dollars');
        $this->assertStringNotContainsString('$30', $html, 'the total is still in dollars');
        $this->assertStringNotContainsString('US$', $html);
    }

    public function test_the_prices_are_labelled_as_riyals(): void
    {
        $html = $this->get(AppointmentResource::getUrl('view', ['record' => $this->booking()]))
            ->getContent();

        // Intl renders SAR as "SAR" or "ر.س" depending on locale; either is a
        // riyal, and neither is a dollar.
        $this->assertTrue(
            str_contains($html, 'SAR') || str_contains($html, 'ر.س'),
            'no riyal marker appears anywhere on the page',
        );
    }

    public function test_every_money_entry_in_the_panel_names_or_inherits_riyals(): void
    {
        // Guards the whole panel, not just this page: a bare ->money() is fine
        // now that the panel defaults to SAR, but an explicit foreign currency
        // would be a real mistake.
        $offenders = [];

        foreach ($this->phpFilesIn(app_path('Filament')) as $file) {
            $contents = file_get_contents($file);

            if (preg_match_all("/->money\(\s*'([a-zA-Z]{3})'/", $contents, $matches)) {
                foreach ($matches[1] as $currency) {
                    if (strtoupper($currency) !== 'SAR') {
                        $offenders[] = basename($file).": {$currency}";
                    }
                }
            }
        }

        $this->assertSame([], $offenders, 'a non-riyal currency is hard-coded in the panel');
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
