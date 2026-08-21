<?php

namespace Tests\Feature;

use App\Filament\Resources\Customers\CustomerResource;
use App\Filament\Resources\Customers\RelationManagers\AppointmentsRelationManager;
use App\Filament\Resources\Customers\RelationManagers\PackagesRelationManager;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\CustomerDevice;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\User;
use App\Support\CustomerProfile;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The customer page showed name, phone, city and a note. It answered none of
 * what is actually asked before offering a plan, a refund or a goodwill wash:
 * what this customer is worth, whether they turn up, and when they were last
 * seen. It also had no way to reach their bookings.
 */
class CustomerProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        CustomerProfile::forget();

        $user = User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-for-tests',
        ]);
        // Customer is policy-guarded, so the role needs the permissions the
        // policy asks for — assigning the role alone leaves the page 403.
        $role = Role::create(['name' => 'super_admin', 'guard_name' => 'web']);
        foreach (['ViewAny:Customer', 'View:Customer', 'Update:Customer'] as $permission) {
            $role->givePermissionTo(Permission::create(['name' => $permission, 'guard_name' => 'web']));
        }
        $user->assignRole($role);

        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
    }

    private function customer(array $overrides = []): Customer
    {
        static $n = 0;
        $n++;

        return Customer::create(array_merge([
            'name' => "Customer {$n}",
            'phone' => '+96650000'.str_pad((string) $n, 4, '0', STR_PAD_LEFT),
            'status' => 'active',
            'city' => 'Riyadh',
            'preferred_language' => 'ar',
            'wallet_balance' => 0,
        ], $overrides));
    }

    private function booking(Customer $customer, string $status, float $total, string $scheduledAt): Appointment
    {
        return Appointment::create([
            'customer_id' => $customer->id,
            'status' => $status,
            'scheduled_at' => $scheduledAt,
            'service_name' => 'Express exterior wash',
            'service_name_ar' => 'غسيل خارجي سريع',
            'base_price' => $total,
            'addons_total' => 0,
            'discount_total' => 0,
            'total_price' => $total,
            'payment_method' => 'wallet',
            'payment_status' => 'paid',
        ]);
    }

    public function test_lifetime_spend_counts_only_completed_visits(): void
    {
        // A cancelled booking was refunded and a future one is not earned yet;
        // counting either would overstate what this customer is worth.
        $customer = $this->customer();
        $this->booking($customer, Appointment::STATUS_COMPLETED, 40, '2026-08-01 16:00:00');
        $this->booking($customer, Appointment::STATUS_COMPLETED, 60, '2026-08-05 16:00:00');
        $this->booking($customer, Appointment::STATUS_CANCELLED, 999, '2026-08-06 16:00:00');
        $this->booking($customer, Appointment::STATUS_CONFIRMED, 999, '2099-01-01 16:00:00');

        $profile = CustomerProfile::for($customer);

        $this->assertSame(100.0, $profile->spend);
        $this->assertSame(50.0, $profile->averageOrder());
        $this->assertSame(4, $profile->bookings);
        $this->assertSame(2, $profile->completed);
        $this->assertSame(1, $profile->cancelled);
        $this->assertSame(1, $profile->upcoming);
    }

    public function test_the_cancellation_rate_is_measured_against_every_booking(): void
    {
        $customer = $this->customer();
        $this->booking($customer, Appointment::STATUS_COMPLETED, 40, '2026-08-01 16:00:00');
        $this->booking($customer, Appointment::STATUS_CANCELLED, 40, '2026-08-02 16:00:00');

        $this->assertSame(50.0, CustomerProfile::for($customer)->cancellationRate());
    }

    public function test_a_customer_with_no_bookings_has_no_rate_rather_than_zero(): void
    {
        // 0% would read as a flawless record; there is simply nothing to judge.
        $profile = CustomerProfile::for($this->customer());

        $this->assertNull($profile->cancellationRate());
        $this->assertNull($profile->averageOrder());
        $this->assertSame(0.0, $profile->spend);
    }

    public function test_last_and_next_visit_pick_the_right_bookings(): void
    {
        $customer = $this->customer();
        $this->booking($customer, Appointment::STATUS_COMPLETED, 40, '2026-08-01 16:00:00');
        $recent = $this->booking($customer, Appointment::STATUS_COMPLETED, 40, '2026-08-05 16:00:00');
        $soon = $this->booking($customer, Appointment::STATUS_CONFIRMED, 40, '2099-01-01 16:00:00');
        $this->booking($customer, Appointment::STATUS_CONFIRMED, 40, '2099-06-01 16:00:00');

        $profile = CustomerProfile::for($customer);

        $this->assertSame($recent->id, $profile->lastVisit?->id, 'last visit must be the most recent completed one');
        $this->assertSame($soon->id, $profile->nextVisit?->id, 'next visit must be the soonest upcoming one');
    }

    public function test_promo_redemptions_are_totalled(): void
    {
        $customer = $this->customer();
        $code = PromoCode::create([
            'code' => 'SAVE10',
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 10,
            'min_order_total' => 0,
            'per_customer_limit' => 5,
            'used_count' => 0,
            'is_active' => true,
        ]);
        $booking = $this->booking($customer, Appointment::STATUS_COMPLETED, 40, '2026-08-01 16:00:00');

        PromoCodeRedemption::create([
            'promo_code_id' => $code->id,
            'customer_id' => $customer->id,
            'appointment_id' => $booking->id,
            'amount' => 4.5,
        ]);

        $profile = CustomerProfile::for($customer);

        $this->assertSame(1, $profile->promoRedemptions);
        $this->assertSame(4.5, $profile->promoDiscount);
    }

    public function test_one_customers_figures_do_not_leak_into_another(): void
    {
        // The memo is keyed per customer; sharing it would show the first
        // customer's spend on every profile opened afterwards.
        $rich = $this->customer();
        $this->booking($rich, Appointment::STATUS_COMPLETED, 500, '2026-08-01 16:00:00');
        $poor = $this->customer();

        $this->assertSame(500.0, CustomerProfile::for($rich)->spend);
        $this->assertSame(0.0, CustomerProfile::for($poor)->spend);
    }

    public function test_the_view_page_renders_the_figures(): void
    {
        $customer = $this->customer(['wallet_balance' => 120]);
        $this->booking($customer, Appointment::STATUS_COMPLETED, 40, '2026-08-01 16:00:00');
        $this->booking($customer, Appointment::STATUS_CANCELLED, 40, '2026-08-02 16:00:00');
        CustomerDevice::create([
            'customer_id' => $customer->id,
            'fcm_token' => 'token-1',
            'platform' => 'ios',
        ]);

        $this->get(CustomerResource::getUrl('view', ['record' => $customer]))
            ->assertOk()
            ->assertSee('50%')                    // cancellation rate
            ->assertSee('IOS')                    // a reachable device
            ->assertSee('Express exterior wash'); // the last-visit label
    }

    public function test_a_customer_with_nothing_still_renders(): void
    {
        // Every figure is null or zero here; a page that only works for busy
        // customers is a page that breaks on the day someone signs up.
        $this->get(CustomerResource::getUrl('view', ['record' => $this->customer()]))
            ->assertOk();
    }

    public function test_the_bookings_and_plans_tables_are_reachable_from_the_profile(): void
    {
        // The gap that prompted this: a customer's bookings could not be
        // reached from their own page.
        $relations = CustomerResource::getRelations();

        $this->assertContains(
            AppointmentsRelationManager::class,
            $relations,
        );
        $this->assertContains(
            PackagesRelationManager::class,
            $relations,
        );
    }
}
