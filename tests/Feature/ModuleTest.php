<?php

namespace Tests\Feature;

use App\Enums\ModuleKey;
use App\Enums\Role as RoleName;
use App\Models\Module;
use App\Models\User;
use Database\Seeders\ModuleSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ModuleTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
        foreach ([RoleName::SuperAdmin, RoleName::Admin, RoleName::Finance, RoleName::Staff] as $role) {
            Role::findOrCreate($role->value);
        }
        $this->superAdmin = User::factory()->create()->assignRole(RoleName::SuperAdmin);
    }

    public function test_seeder_creates_every_module_once_with_malay_names_and_writes_no_audit(): void
    {
        $this->seed(ModuleSeeder::class);

        $this->assertSame(count(ModuleKey::cases()), Module::query()->count());
        $payroll = Module::for(ModuleKey::Payroll);
        $this->assertTrue($payroll->exists);
        $this->assertSame('Gaji', $payroll->name_ms);
        $this->assertSame('dollar', $payroll->icon);
        $this->assertTrue($payroll->is_enabled);
        $this->assertTrue($payroll->allowed_roles->isEmpty());

        $payroll->update(['name_en' => 'Salary', 'is_enabled' => false]);
        Activity::query()->delete();
        $this->seed(ModuleSeeder::class);

        $this->assertSame('Salary', Module::for(ModuleKey::Payroll)->name_en);
        $this->assertFalse(Module::for(ModuleKey::Payroll)->is_enabled);
        $this->assertSame(0, Activity::query()->count());
    }

    public function test_modules_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $module = Module::factory()->create();

        $this->assertIsInt($module->id);
        $this->assertTrue(Str::isUlid($module->ulid));
        $this->assertSame($module->ulid, $module->getRouteKey());
    }

    public function test_unsaved_modules_use_their_defaults_and_the_registry_follows_sort_order(): void
    {
        $leave = Module::for(ModuleKey::Leave);
        $this->assertFalse($leave->exists);
        $this->assertSame('Leave', $leave->label());
        $this->assertTrue($leave->isAvailableTo(null));

        Module::factory()->forKey(ModuleKey::Hadir)->create(['sort_order' => 0]);

        $this->assertSame(['hadir', 'leave', 'payroll', 'claim', 'projects'], Module::registry()->keys()->all());
    }

    public function test_label_uses_malay_when_set_and_falls_back_to_english(): void
    {
        $module = Module::factory()->make(['name_en' => 'Payroll', 'name_ms' => 'Gaji']);

        app()->setLocale('ms');
        $this->assertSame('Gaji', $module->label());

        $module->name_ms = null;
        $this->assertSame('Payroll', $module->label());
    }

    public function test_availability_depends_on_the_switch_and_roles(): void
    {
        $finance = User::factory()->create()->assignRole(RoleName::Finance);
        $staff = User::factory()->create()->assignRole(RoleName::Staff);
        $module = Module::factory()->make(['allowed_roles' => [RoleName::Finance]]);

        $this->assertTrue($module->isAvailableTo($finance));
        $this->assertTrue($module->isAvailableTo($this->superAdmin));
        $this->assertFalse($module->isAvailableTo($staff));
        $this->assertFalse($module->isAvailableTo(null));

        $module->is_enabled = false;
        $this->assertFalse($module->isAvailableTo($this->superAdmin));
    }

    public function test_middleware_returns_404_when_off_and_403_for_other_roles(): void
    {
        Route::middleware(['web', 'auth', 'module:payroll'])->get('/_test/payroll', fn (): string => 'payroll page');
        $finance = User::factory()->create()->assignRole(RoleName::Finance);
        $staff = User::factory()->create()->assignRole(RoleName::Staff);

        $this->actingAs($staff)->get('/_test/payroll')->assertOk()->assertSee('payroll page');

        $module = Module::factory()->create(['allowed_roles' => [RoleName::Finance]]);
        $this->actingAs($staff)->get('/_test/payroll')->assertForbidden();
        $this->actingAs($finance)->get('/_test/payroll')->assertOk();

        $module->update(['is_enabled' => false]);
        $this->actingAs($finance)->get('/_test/payroll')->assertNotFound();
        $this->actingAs($this->superAdmin)->get('/_test/payroll')->assertNotFound();
    }

    public function test_switching_a_module_off_hides_its_settings_and_returns_404(): void
    {
        $this->actingAs($this->superAdmin)->get(route('settings.payroll.general'))->assertOk();

        Module::factory()->disabled()->create();

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.general'))->assertNotFound();
        $this->actingAs($this->superAdmin)->put(route('settings.payroll.general.update'), [])->assertNotFound();
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))
            ->assertDontSee(route('settings.payroll.general'))
            ->assertSee(route('settings.claim.general'));
    }

    public function test_sidebar_uses_module_names_icons_and_order(): void
    {
        Module::factory()->forKey(ModuleKey::Hadir)->create(['name_en' => 'Attendance', 'icon' => 'users', 'sort_order' => 1]);
        Module::factory()->forKey(ModuleKey::Leave)->create(['sort_order' => 9]);

        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSeeInOrder(['Company', 'Attendance', 'Payroll', 'Claim', 'Leave'])
            ->assertSee('icons.svg#i-users', false);
    }

    public function test_guests_are_redirected_and_other_roles_are_forbidden(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->get(route('administrations.modules.index'))->assertRedirect(route('login'));
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('administrations.modules.index'));
        $this->actingAs($admin)->get(route('administrations.modules.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('administrations.modules.edit', ModuleKey::Payroll))->assertForbidden();
        $this->actingAs($admin)->put(route('administrations.modules.update', ModuleKey::Payroll), $this->payload())->assertForbidden();
    }

    public function test_index_lists_modules_with_status_and_roles(): void
    {
        Module::factory()->create(['allowed_roles' => [RoleName::Finance], 'is_enabled' => false]);

        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('administrations.modules.index'));

        $this->actingAs($this->superAdmin)->get(route('administrations.modules.index'))
            ->assertOk()
            ->assertSeeInOrder(['Leave', 'Everyone', 'Payroll', 'Off', 'Finance', 'and Super Admin', 'Claim', 'Hadir'])
            ->assertSee(route('administrations.modules.edit', ModuleKey::Hadir));
    }

    public function test_update_saves_the_module_in_role_order_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)->get(route('administrations.modules.edit', ModuleKey::Payroll))
            ->assertOk()
            ->assertSee('value="Payroll"', false)
            ->assertDontSee('value="Super Admin"', false);

        $this->actingAs($this->superAdmin)
            ->put(route('administrations.modules.update', ModuleKey::Payroll), $this->payload([
                'name_en' => '  Salary ',
                'name_ms' => ' ',
                'icon' => 'card',
                'is_enabled' => '0',
                'allowed_roles' => ['Finance', 'Director'],
            ]))
            ->assertRedirect(route('administrations.modules.index'))
            ->assertSessionHas('status', 'Salary was updated.');

        $module = Module::for(ModuleKey::Payroll);
        $this->assertSame('Salary', $module->name_en);
        $this->assertNull($module->name_ms);
        $this->assertSame('card', $module->icon);
        $this->assertFalse($module->is_enabled);
        $this->assertSame([RoleName::Director, RoleName::Finance], $module->allowed_roles->all());

        $activity = Activity::query()->whereMorphedTo('subject', $module)->sole();
        $this->assertSame('Created module', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));

        $this->actingAs($this->superAdmin)
            ->put(route('administrations.modules.update', ModuleKey::Payroll), $this->payload(['name_en' => 'Salary', 'name_ms' => '', 'icon' => 'card', 'is_enabled' => '1', 'allowed_roles' => ['Finance', 'Director']]));

        $this->assertSame(['is_enabled' => true], Activity::query()->latest('id')->first()->attribute_changes['attributes']);
        $this->assertSame('Updated module', Activity::query()->latest('id')->first()->description);
    }

    public function test_unknown_module_key_is_not_found(): void
    {
        $this->actingAs($this->superAdmin)->get('/administrations/modules/inventory/edit')->assertNotFound();
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidModules')]
    public function test_invalid_modules_are_rejected(array $overrides, string $field): void
    {
        Module::factory()->forKey(ModuleKey::Claim)->create();

        $this->actingAs($this->superAdmin)
            ->from(route('administrations.modules.edit', ModuleKey::Payroll))
            ->put(route('administrations.modules.update', ModuleKey::Payroll), $this->payload($overrides))
            ->assertRedirect(route('administrations.modules.edit', ModuleKey::Payroll))
            ->assertSessionHasErrors($field);

        $this->assertFalse(Module::for(ModuleKey::Payroll)->exists);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidModules(): array
    {
        return [
            'missing name' => [['name_en' => '  '], 'name_en'],
            'name too long' => [['name_en' => str_repeat('a', 41)], 'name_en'],
            'name used by another module' => [['name_en' => 'Claim'], 'name_en'],
            'unknown icon' => [['icon' => 'rocket'], 'icon'],
            'order out of range' => [['sort_order' => '100'], 'sort_order'],
            'unknown role' => [['allowed_roles' => ['Astronaut']], 'allowed_roles.0'],
            'super admin offered as a role' => [['allowed_roles' => ['Super Admin']], 'allowed_roles.0'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'name_en' => 'Payroll',
            'name_ms' => 'Gaji',
            'icon' => 'dollar',
            'sort_order' => '2',
            'is_enabled' => '1',
        ], $overrides);
    }
}
