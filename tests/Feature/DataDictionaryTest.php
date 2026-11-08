<?php

namespace Tests\Feature;

use App\Enums\LookupCategory;
use App\Enums\Role as RoleName;
use App\Models\LookupItem;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class DataDictionaryTest extends TestCase
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

    public function test_other_roles_are_forbidden(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('administrations.data-dictionary.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('administrations.data-dictionary.show', LookupCategory::MalaysianStates))->assertForbidden();
    }

    public function test_index_lists_categories_with_active_and_inactive_counts(): void
    {
        $this->seed(LookupSeeder::class);
        LookupItem::query()->where('code', 'labuan')->update(['is_active' => false]);

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.data-dictionary.index'))
            ->assertOk()
            ->assertSee('Malaysian States')
            ->assertSee('malaysian-states')
            ->assertSee(route('administrations.data-dictionary.show', LookupCategory::MalaysianStates))
            ->assertSeeInOrder(['15', 'active', '1 inactive']);
    }

    public function test_category_page_lists_items_in_display_order_with_status(): void
    {
        LookupItem::factory()->create(['code' => 'second', 'label_en' => 'Second item', 'sort_order' => 20]);
        LookupItem::factory()->inactive()->create(['code' => 'retired', 'label_en' => 'Retired item', 'sort_order' => 30]);
        LookupItem::factory()->create(['code' => 'first', 'label_en' => 'First item', 'label_ms' => 'Item pertama', 'sort_order' => 10]);

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.data-dictionary.show', LookupCategory::MalaysianStates))
            ->assertOk()
            ->assertSeeInOrder(['First item', 'Item pertama', 'Second item', 'Uses English', 'Retired item', 'Inactive']);
    }

    public function test_unknown_category_returns_not_found(): void
    {
        $this->actingAs($this->superAdmin)->get('/administrations/data-dictionary/not-a-category')->assertNotFound();
    }

    public function test_options_return_active_items_in_order_in_the_current_language(): void
    {
        LookupItem::factory()->create(['code' => 'penang', 'label_en' => 'Penang', 'label_ms' => 'Pulau Pinang', 'sort_order' => 20]);
        LookupItem::factory()->create(['code' => 'johor', 'label_en' => 'Johor', 'sort_order' => 10]);
        LookupItem::factory()->inactive()->create(['code' => 'old', 'label_en' => 'Old', 'sort_order' => 5]);

        $this->assertSame(['johor' => 'Johor', 'penang' => 'Penang'], LookupItem::options(LookupCategory::MalaysianStates));

        $this->app->setLocale('ms');
        $this->assertSame(['johor' => 'Johor', 'penang' => 'Pulau Pinang'], LookupItem::options(LookupCategory::MalaysianStates));
    }

    public function test_seeder_adds_all_states_once_keeps_edits_and_writes_no_audit_entries(): void
    {
        $this->seed(LookupSeeder::class);
        LookupItem::query()->where('code', 'penang')->update(['label_en' => 'Penang Island']);
        Activity::query()->delete();

        $this->seed(LookupSeeder::class);

        $this->assertSame(16, LookupItem::query()->inCategory(LookupCategory::MalaysianStates)->count());
        $this->assertSame('Penang Island', LookupItem::query()->where('code', 'penang')->value('label_en'));
        $this->assertSame(0, Activity::query()->count());
    }

    public function test_item_changes_are_audited(): void
    {
        $item = LookupItem::factory()->create(['label_en' => 'Selangor']);

        $this->actingAs($this->superAdmin);
        $item->update(['is_active' => false]);

        $activity = Activity::query()->where('event', 'updated')->sole();
        $this->assertSame('Updated Selangor in the data dictionary', $activity->description);
        $this->assertSame(['is_active' => false], $activity->attribute_changes['attributes']);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_menu_shows_data_dictionary_to_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.dashboard'))
            ->assertSee(route('administrations.data-dictionary.index'));
    }
}
