<?php

namespace Tests\Feature;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The admin panel returned 403 to everyone in production, including the super
 * admin holding all 313 permissions.
 *
 * Filament only skips the panel-access check outside production. With
 * APP_ENV=production, a user model that does not implement FilamentUser is
 * denied outright — no permission or role can override it, because the check
 * happens before authorization is consulted. It worked locally for exactly that
 * reason, which is what made it hard to see.
 */
class PanelAccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => 'admin@example.test',
            'password' => 'secret-for-tests',
        ]);
    }

    public function test_the_user_model_implements_the_contract_filament_requires(): void
    {
        // Without this, production denies the panel regardless of roles.
        $this->assertInstanceOf(FilamentUser::class, $this->user());
    }

    public function test_a_user_with_a_role_may_open_the_panel(): void
    {
        $user = $this->user();
        $user->assignRole(Role::create(['name' => 'super_admin', 'guard_name' => 'web']));

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));
    }

    /**
     * A row in `users` is not an admin. Customers and workers live in their own
     * tables, but anything that ever creates a User must not thereby hand out
     * the panel.
     */
    public function test_a_user_with_no_role_is_refused(): void
    {
        $this->assertFalse($this->user()->canAccessPanel(Filament::getPanel('admin')));
    }

    public function test_removing_the_last_role_removes_access(): void
    {
        $user = $this->user();
        $role = Role::create(['name' => 'ops', 'guard_name' => 'web']);
        $user->assignRole($role);

        $this->assertTrue($user->canAccessPanel(Filament::getPanel('admin')));

        $user->removeRole($role);

        $this->assertFalse($user->fresh()->canAccessPanel(Filament::getPanel('admin')));
    }
}
