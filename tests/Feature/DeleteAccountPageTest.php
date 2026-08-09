<?php

namespace Tests\Feature;

use App\Models\AppSetting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Google Play's Data safety form points at this URL and the reviewer opens it.
 * If it 404s or needs a login, the app is rejected — so the page must stay
 * public and must keep saying how to request deletion.
 */
class DeleteAccountPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_page_is_public(): void
    {
        // No auth: a reviewer, and a user who can no longer sign in, must reach it.
        $this->get('/delete-account')->assertOk();
    }

    public function test_it_explains_the_in_app_route_and_the_no_app_route(): void
    {
        $response = $this->get('/delete-account');

        $response->assertSee('حذف الحساب', escape: false);
        $response->assertSee('طلب الحذف بدون التطبيق', escape: false);
        // The English half exists for the reviewer.
        $response->assertSee('Delete your Velto account', escape: false);
    }

    public function test_it_uses_the_configured_support_contacts(): void
    {
        AppSetting::updateOrCreate(
            ['key' => 'support.email_support'],
            ['group' => 'support', 'value' => 'help@example.test', 'type' => 'email'],
        );

        $this->get('/delete-account')->assertSee('help@example.test', escape: false);
    }

    public function test_it_falls_back_when_no_support_email_is_configured(): void
    {
        AppSetting::query()->where('key', 'like', 'support.email%')->delete();

        // A missing setting must not produce a page with no way to ask.
        $this->get('/delete-account')
            ->assertOk()
            ->assertSee('support@velto.sa', escape: false);
    }

    public function test_it_states_what_is_deleted_and_what_is_kept(): void
    {
        // Play requires the retention disclosure, not just the deletion promise.
        $this->get('/delete-account')
            ->assertSee('ما الذي يُحذف', escape: false)
            ->assertSee('ما الذي يبقى ولماذا', escape: false);
    }
}
