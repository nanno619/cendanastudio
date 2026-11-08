<?php

namespace Tests\Feature;

use App\Enums\Role as RoleName;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class RoleSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        config(['cendana.seed_admin' => ['name' => 'Owner', 'email' => 'owner@example.test', 'password' => 'a-long-secret-pass']]);
    }

    public function test_seeds_every_role_once(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $this->assertEqualsCanonicalizing(
            array_column(RoleName::cases(), 'value'),
            Role::pluck('name')->all(),
        );
    }

    public function test_seeded_admin_is_super_admin(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->assertTrue(User::firstWhere('email', 'owner@example.test')->hasRole(RoleName::SuperAdmin));
    }

    public function test_super_admin_passes_any_permission_check(): void
    {
        $this->seed(DatabaseSeeder::class);
        Permission::create(['name' => 'approve invoices']);

        $this->assertTrue(User::firstWhere('email', 'owner@example.test')->can('approve invoices'));
        $this->assertTrue(User::firstWhere('email', 'owner@example.test')->can('an ability nobody defined'));
        $this->assertFalse(User::factory()->create()->assignRole(RoleName::Director)->can('approve invoices'));
    }

    public function test_seeds_one_user_per_role_outside_production(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        foreach (RoleName::cases() as $role) {
            $this->assertSame(1, User::role($role)->count(), $role->value);
        }
        $this->assertTrue(User::firstWhere('email', 'project-manager@demo.test')->hasRole(RoleName::ProjectManager));
    }

    public function test_skips_role_users_in_production(): void
    {
        $this->app->detectEnvironment(fn () => 'production');

        $this->artisan('db:seed', ['--force' => true])->assertSuccessful();

        $this->assertSame(1, User::count());
    }

    public function test_role_users_never_overwrite_the_seeded_admin(): void
    {
        config(['cendana.seed_admin.email' => 'admin@demo.test']);

        $this->seed(DatabaseSeeder::class);

        $admin = User::firstWhere('email', 'admin@demo.test');
        $this->assertSame(['Super Admin'], $admin->getRoleNames()->all());
        $this->assertSame('Owner', $admin->name);
    }
}
