<?php

namespace Tests\Feature;

use App\Filament\Resources\PromoCodes\Pages\CreatePromoCode;
use App\Filament\Resources\PromoCodes\PromoCodeResource;
use App\Models\PromoCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Filament\Facades\Filament;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The promo code screens shipped without a create button and without any way to
 * delete a single code: the create page existed at its URL but nothing linked
 * to it, so a code could not be made from the admin at all.
 */
class PromoCodeCrudTest extends TestCase
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

    private function promoCode(array $overrides = []): PromoCode
    {
        return PromoCode::create(array_merge([
            'code' => 'SAVE10',
            'type' => PromoCode::TYPE_PERCENT,
            'value' => 10,
            'min_order_total' => 0,
            'per_customer_limit' => 1,
            'used_count' => 0,
            'is_active' => true,
        ], $overrides));
    }

    public function test_the_list_page_offers_a_way_to_create(): void
    {
        // The regression: without a header CreateAction nothing links to the
        // create page, so the resource is read-only in practice. Assert the
        // link rather than the label, which Filament derives from the model
        // name and would break this test on a rename.
        $this->get(PromoCodeResource::getUrl('index'))
            ->assertOk()
            ->assertSee('promo-codes/create', escape: false);
    }

    public function test_the_create_page_loads(): void
    {
        $this->get(PromoCodeResource::getUrl('create'))->assertOk();
    }

    public function test_the_edit_page_offers_a_way_to_delete(): void
    {
        $code = $this->promoCode();

        $this->get(PromoCodeResource::getUrl('edit', ['record' => $code]))
            ->assertOk()
            ->assertSee('Delete');
    }

    public function test_the_list_page_renders_existing_codes(): void
    {
        $this->promoCode(['code' => 'WELCOME20']);

        $this->get(PromoCodeResource::getUrl('index'))
            ->assertOk()
            ->assertSee('WELCOME20');
    }

    public function test_codes_are_matched_case_insensitively(): void
    {
        // The form uppercases on save and the model looks up with UPPER(),
        // so a customer typing lowercase must still find the code.
        $this->promoCode(['code' => 'SAVE10']);

        $this->assertNotNull(PromoCode::findByCode('save10'));
        $this->assertNotNull(PromoCode::findByCode('  SaVe10 '));
        $this->assertNull(PromoCode::findByCode('nope'));
    }

    public function test_a_code_outside_its_window_is_not_redeemable(): void
    {
        $expired = $this->promoCode([
            'code' => 'OLD',
            'expires_at' => now()->subDay(),
        ]);
        $future = $this->promoCode([
            'code' => 'SOON',
            'starts_at' => now()->addDay(),
        ]);
        $live = $this->promoCode([
            'code' => 'NOW',
            'starts_at' => now()->subDay(),
            'expires_at' => now()->addDay(),
        ]);

        $this->assertFalse($expired->withinWindow());
        $this->assertFalse($future->withinWindow());
        $this->assertTrue($live->withinWindow());
    }

    public function test_creating_a_code_through_the_form_actually_saves_it(): void
    {
        // The earlier tests only proved the pages loaded. They did not submit
        // anything, so nobody noticed that every code saved as an empty string
        // — Filament injects closure arguments by NAME, and the dehydrate
        // closure named its parameter $s, which received null.
        Livewire::test(CreatePromoCode::class)
            ->fillForm([
                'code' => 'save10',
                'type' => PromoCode::TYPE_PERCENT,
                'value' => 15,
                'min_order_total' => 0,
                'per_customer_limit' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $saved = PromoCode::first();

        $this->assertNotNull($saved);
        $this->assertSame('SAVE10', $saved->code, 'the code must be stored, and uppercased');
    }

    public function test_a_saved_code_can_actually_be_redeemed(): void
    {
        // An empty code is not merely untidy: findByCode() returns null for it,
        // so the promo could never be applied to a booking.
        Livewire::test(CreatePromoCode::class)
            ->fillForm([
                'code' => 'welcome20',
                'type' => PromoCode::TYPE_PERCENT,
                'value' => 20,
                'min_order_total' => 0,
                'per_customer_limit' => 1,
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertNotNull(PromoCode::findByCode('WELCOME20'));
        $this->assertNotNull(PromoCode::findByCode('welcome20'));
    }
}
