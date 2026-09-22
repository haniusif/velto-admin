<?php

namespace Tests\Feature;

use App\Filament\Resources\PromoCodes\Pages\ViewPromoCode;
use App\Filament\Resources\PromoCodes\RelationManagers\RedemptionsRelationManager;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\PromoCode;
use App\Models\PromoCodeRedemption;
use App\Models\User;
use Filament\Facades\Filament;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A promo code's page has to answer "who used this, and on which booking?" —
 * the list screen only ever showed a bare counter.
 */
class PromoCodeRedemptionsViewTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::create(['name' => 'Admin', 'email' => 'admin@example.test', 'password' => 'secret-for-tests']);
        $user->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));
        $this->actingAs($user);
        Filament::setCurrentPanel('admin');
    }

    private function seedRedemptions(): array
    {
        $promo = PromoCode::create([
            'code' => 'LAUNCH15', 'type' => PromoCode::TYPE_PERCENT, 'value' => 15,
            'usage_limit' => 50, 'used_count' => 2, 'is_active' => true,
        ]);

        $rows = [];
        foreach ([['Hani', '+966500000001', 12.5], ['Sara', '+966500000002', 7.5]] as [$name, $phone, $amount]) {
            $customer = Customer::create(['name' => $name, 'phone' => $phone, 'status' => 'active']);
            $appointment = Appointment::create([
                'customer_id' => $customer->id,
                'service_name' => 'Express exterior wash',
                'scheduled_at' => now()->addDay(),
                'status' => Appointment::STATUS_CONFIRMED,
                'total_price' => 50,
            ]);
            $rows[] = PromoCodeRedemption::create([
                'promo_code_id' => $promo->id,
                'customer_id' => $customer->id,
                'appointment_id' => $appointment->id,
                'amount' => $amount,
            ]);
        }

        return [$promo, $rows];
    }

    public function test_the_view_page_opens_and_shows_the_code(): void
    {
        [$promo] = $this->seedRedemptions();

        $this->get("/admin/promo-codes/{$promo->id}")
            ->assertSuccessful()
            ->assertSee('LAUNCH15');
    }

    public function test_it_lists_each_customer_with_their_booking_number(): void
    {
        [$promo, $rows] = $this->seedRedemptions();

        Livewire::test(RedemptionsRelationManager::class, [
            'ownerRecord' => $promo,
            'pageClass' => ViewPromoCode::class,
        ])
            ->assertSuccessful()
            ->assertCanSeeTableRecords($rows)
            ->assertSee('Hani')
            ->assertSee('Sara')
            // The booking numbers the discounts were spent on.
            ->assertSee((string) $rows[0]->appointment_id)
            ->assertSee((string) $rows[1]->appointment_id);
    }

    public function test_the_relation_badge_counts_redemptions(): void
    {
        [$promo] = $this->seedRedemptions();

        $this->assertSame('2', RedemptionsRelationManager::getBadge($promo, ViewPromoCode::class));
    }

    public function test_a_code_nobody_used_says_so_instead_of_showing_an_empty_table(): void
    {
        $promo = PromoCode::create(['code' => 'UNUSED', 'type' => PromoCode::TYPE_FIXED, 'value' => 5, 'is_active' => true]);

        Livewire::test(RedemptionsRelationManager::class, ['ownerRecord' => $promo, 'pageClass' => ViewPromoCode::class])
            ->assertSuccessful()
            ->assertSee('Not used yet');

        $this->assertNull(RedemptionsRelationManager::getBadge($promo, ViewPromoCode::class));
    }
}
