<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class LogViewerTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        Role::findOrCreate(RoleName::SuperAdmin->value);
        Role::findOrCreate(RoleName::Admin->value);
        $this->superAdmin = User::factory()->create()->assignRole(RoleName::SuperAdmin);
    }

    public function test_super_admin_sees_the_embedded_viewer_and_menu_item(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('administrations.log-viewer.index'))
            ->assertOk()
            ->assertSee('<iframe src="'.route('log-viewer.index').'"', false)
            ->assertSee(route('administrations.log-viewer.index'));

        $this->get(route('log-viewer.index'))->assertOk();
        $this->getJson(route('log-viewer.files'))->assertOk();
    }

    public function test_other_roles_are_forbidden(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('administrations.log-viewer.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('log-viewer.index'))->assertForbidden();
        $this->actingAs($admin)->getJson(route('log-viewer.files'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('administrations.log-viewer.index'));
    }

    public function test_other_roles_are_forbidden_in_production(): void
    {
        $this->app->detectEnvironment(fn (): string => 'production');
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('log-viewer.index'))->assertForbidden();
        $this->actingAs($this->superAdmin)->get(route('log-viewer.index'))->assertOk();
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('administrations.log-viewer.index'))->assertRedirect(route('login'));
        $this->get(route('log-viewer.index'))->assertRedirect(route('login'));
        $this->getJson(route('log-viewer.files'))->assertUnauthorized();
    }
}
