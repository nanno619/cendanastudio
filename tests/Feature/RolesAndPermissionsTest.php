<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RolesAndPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();

        Route::middleware(['web', 'auth', 'role:admin'])->get('/_test/admin-only', fn () => 'admin area');
        Route::middleware(['web', 'auth', 'permission:edit records'])->get('/_test/edit-records', fn () => 'editor area');
    }

    public function test_permissions_granted_through_a_role_pass_the_gate(): void
    {
        $role = Role::create(['name' => 'editor']);
        $role->givePermissionTo(Permission::create(['name' => 'edit records']));

        $user = User::factory()->create();
        $user->assignRole('editor');

        $this->assertTrue($user->hasRole('editor'));
        $this->assertTrue($user->can('edit records'));
        $this->assertFalse(User::factory()->create()->can('edit records'));
    }

    public function test_role_middleware_allows_only_matching_users(): void
    {
        Role::create(['name' => 'admin']);
        $admin = User::factory()->create()->assignRole('admin');

        $this->actingAs($admin)->get('/_test/admin-only')->assertOk()->assertSee('admin area');
        $this->actingAs(User::factory()->create())->get('/_test/admin-only')->assertForbidden();
    }

    public function test_permission_middleware_allows_only_permitted_users(): void
    {
        $user = User::factory()->create()->givePermissionTo(Permission::create(['name' => 'edit records']));

        $this->actingAs($user)->get('/_test/edit-records')->assertOk();
        $this->actingAs(User::factory()->create())->get('/_test/edit-records')->assertForbidden();
    }
}
