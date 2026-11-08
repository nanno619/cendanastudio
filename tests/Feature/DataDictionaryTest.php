<?php

namespace Tests\Feature;

use App\Enums\LookupCategory;
use App\Enums\Role as RoleName;
use App\Models\LookupItem;
use App\Models\User;
use Database\Seeders\LookupSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
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

    public function test_seeder_fills_every_category(): void
    {
        $this->seed(LookupSeeder::class);

        foreach (LookupCategory::cases() as $category) {
            $this->assertNotEmpty(LookupItem::options($category), $category->value);
        }
        $this->assertSame(['fuel', 'stationery', 'stamping', 'meal', 'toll', 'accommodation', 'medical'], array_keys(LookupItem::options(LookupCategory::ClaimType)));
        $this->assertSame('success', LookupItem::query()->inCategory(LookupCategory::Statuses)->where('code', 'approved')->value('tone'));
    }

    public function test_items_cannot_be_reached_through_another_category(): void
    {
        $gender = LookupItem::factory()->create(['category' => LookupCategory::Gender, 'code' => 'male', 'label_en' => 'Male']);

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.data-dictionary.items.edit', [LookupCategory::Race, $gender]))
            ->assertNotFound();

        $this->actingAs($this->superAdmin)
            ->patch(route('administrations.data-dictionary.items.status', [LookupCategory::Race, $gender]))
            ->assertNotFound();

        $this->assertTrue($gender->fresh()->is_active);
    }

    public function test_item_changes_are_audited(): void
    {
        $item = LookupItem::factory()->create(['label_en' => 'Selangor']);

        $this->actingAs($this->superAdmin);
        $item->update(['is_active' => false]);

        $activity = Activity::query()->where('event', 'updated')->sole();
        $this->assertSame('Deactivated Selangor in the data dictionary', $activity->description);
        $this->assertSame(['is_active' => false], $activity->attribute_changes['attributes']);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_menu_shows_data_dictionary_to_super_admin(): void
    {
        $this->actingAs($this->superAdmin)
            ->get(route('admin.dashboard'))
            ->assertSee(route('administrations.data-dictionary.index'));
    }

    public function test_add_form_suggests_the_next_display_order(): void
    {
        LookupItem::factory()->create(['sort_order' => 40]);

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.data-dictionary.items.create', LookupCategory::MalaysianStates))
            ->assertOk()
            ->assertSee('value="50"', false);
    }

    public function test_super_admin_can_add_an_item(): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('administrations.data-dictionary.items.store', LookupCategory::MalaysianStates), $this->payload())
            ->assertRedirect(route('administrations.data-dictionary.show', LookupCategory::MalaysianStates))
            ->assertSessionHas('status', 'Kuala Lumpur was added.');

        $item = LookupItem::query()->sole();
        $this->assertSame(LookupCategory::MalaysianStates, $item->category);
        $this->assertSame(['kuala-lumpur', 'Kuala Lumpur', 'KL', 15, 'info', true], [$item->code, $item->label_en, $item->label_ms, $item->sort_order, $item->tone, $item->is_active]);
        $this->assertTrue(Activity::query()->where('event', 'created')->where('subject_type', $item->getMorphClass())->sole()->causer->is($this->superAdmin));
    }

    public function test_other_roles_cannot_add_items(): void
    {
        $this->actingAs(User::factory()->create()->assignRole(RoleName::Admin))
            ->post(route('administrations.data-dictionary.items.store', LookupCategory::MalaysianStates), $this->payload())
            ->assertForbidden();

        $this->assertSame(0, LookupItem::query()->count());
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidItems(): array
    {
        return [
            'missing English label' => [['label_en' => ''], 'label_en'],
            'code with spaces' => [['code' => 'Kuala Lumpur'], 'code'],
            'code with capitals' => [['code' => 'KL'], 'code'],
            'negative order' => [['sort_order' => -1], 'sort_order'],
            'unknown badge colour' => [['tone' => 'purple'], 'tone'],
        ];
    }

    #[DataProvider('invalidItems')]
    public function test_invalid_items_are_rejected(array $override, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->post(route('administrations.data-dictionary.items.store', LookupCategory::MalaysianStates), [...$this->payload(), ...$override])
            ->assertSessionHasErrors($field);

        $this->assertSame(0, LookupItem::query()->count());
    }

    public function test_code_and_english_label_must_be_unique_within_the_category(): void
    {
        LookupItem::factory()->create(['code' => 'kuala-lumpur', 'label_en' => 'Kuala Lumpur']);

        $this->actingAs($this->superAdmin)
            ->post(route('administrations.data-dictionary.items.store', LookupCategory::MalaysianStates), $this->payload())
            ->assertSessionHasErrors([
                'code' => 'This category already has an item with this code.',
                'label_en' => 'This category already has an item with this English label.',
            ]);
    }

    public function test_editing_updates_labels_but_never_the_code(): void
    {
        $item = LookupItem::factory()->create(['code' => 'penang', 'label_en' => 'Penang']);

        $this->actingAs($this->superAdmin)
            ->get(route('administrations.data-dictionary.items.edit', [LookupCategory::MalaysianStates, $item]))
            ->assertOk()
            ->assertSee('penang')
            ->assertDontSee('name="code"', false);

        $this->actingAs($this->superAdmin)
            ->put(route('administrations.data-dictionary.items.update', [LookupCategory::MalaysianStates, $item]), [
                ...$this->payload(), 'code' => 'changed', 'label_en' => 'Penang Island', 'label_ms' => 'Pulau Pinang',
            ])
            ->assertRedirect(route('administrations.data-dictionary.show', LookupCategory::MalaysianStates));

        $item->refresh();
        $this->assertSame(['penang', 'Penang Island', 'Pulau Pinang'], [$item->code, $item->label_en, $item->label_ms]);
    }

    public function test_editing_keeps_the_items_own_label_unique_check_out_of_the_way(): void
    {
        $item = LookupItem::factory()->create(['label_en' => 'Johor']);

        $this->actingAs($this->superAdmin)
            ->put(route('administrations.data-dictionary.items.update', [LookupCategory::MalaysianStates, $item]), [...$this->payload(), 'label_en' => 'Johor'])
            ->assertSessionHasNoErrors();
    }

    public function test_status_button_deactivates_and_reactivates_an_item(): void
    {
        $item = LookupItem::factory()->create(['label_en' => 'Labuan']);
        $route = route('administrations.data-dictionary.items.status', [LookupCategory::MalaysianStates, $item]);

        $this->actingAs($this->superAdmin)->patch($route)
            ->assertSessionHas('status', 'Labuan was deactivated and is hidden from dropdowns.');
        $this->assertFalse($item->fresh()->is_active);
        $this->assertSame([], LookupItem::options(LookupCategory::MalaysianStates));
        $this->assertSame('Deactivated Labuan in the data dictionary', Activity::query()->latest('id')->value('description'));

        $this->actingAs($this->superAdmin)->patch($route);
        $this->assertTrue($item->fresh()->is_active);
        $this->assertSame('Activated Labuan in the data dictionary', Activity::query()->latest('id')->value('description'));
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(): array
    {
        return ['code' => 'kuala-lumpur', 'label_en' => 'Kuala Lumpur', 'label_ms' => 'KL', 'sort_order' => 15, 'tone' => 'info', 'is_active' => '1'];
    }
}
