<?php

namespace Tests\Feature;

use App\Enums\ClaimPaymentMethod;
use App\Enums\LookupCategory;
use App\Enums\Role as RoleName;
use App\Models\ClaimSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ClaimSettingsTest extends TestCase
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

    public function test_current_returns_defaults_then_the_saved_row(): void
    {
        $default = ClaimSetting::current();
        $this->assertFalse($default->exists);
        $this->assertSame(30, $default->submission_deadline_days);
        $this->assertSame('0.00', $default->receipt_required_above);
        $this->assertTrue($default->allow_resubmission);
        $this->assertFalse($default->mileage_enabled);
        $this->assertNull($default->mileage_rate);
        $this->assertSame(ClaimPaymentMethod::WithPayroll, $default->payment_method);

        $saved = ClaimSetting::factory()->create(['submission_deadline_days' => 60]);
        $this->assertTrue(ClaimSetting::current()->is($saved));
        $this->assertSame(60, ClaimSetting::current()->submission_deadline_days);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = ClaimSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_deadline_and_receipt_rules(): void
    {
        $today = CarbonImmutable::parse('2026-11-17 15:00');
        $setting = new ClaimSetting(['submission_deadline_days' => 30, 'receipt_required_above' => '0.00']);

        $this->assertTrue($setting->acceptsExpenseDate(CarbonImmutable::parse('2026-10-18'), $today));
        $this->assertFalse($setting->acceptsExpenseDate(CarbonImmutable::parse('2026-10-17'), $today));
        $this->assertFalse($setting->acceptsExpenseDate(CarbonImmutable::parse('2026-11-18'), $today));
        $this->assertTrue($setting->needsReceipt('1.00'));

        $setting->fill(['submission_deadline_days' => 0, 'receipt_required_above' => '20.00']);
        $this->assertTrue($setting->acceptsExpenseDate(CarbonImmutable::parse('2024-01-01'), $today));
        $this->assertFalse($setting->needsReceipt('20.00'));
        $this->assertTrue($setting->needsReceipt(20.01));
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.claim.general'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.claim.general'));
        $this->actingAs($admin)->get(route('settings.claim.general'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.claim.general.update'), $this->payload())->assertForbidden();
    }

    public function test_super_admin_sees_the_defaults_and_fixed_rules(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.claim.general'));

        $this->actingAs($this->superAdmin)->get(route('settings.claim.general'))
            ->assertOk()
            ->assertSee('name="submission_deadline_days"', false)
            ->assertSee('value="30"', false)
            ->assertSee('no EPF, SOCSO or EIS is deducted')
            ->assertSee(route('settings.payroll.general'))
            ->assertSee(route('administrations.data-dictionary.show', LookupCategory::ClaimType))
            ->assertSee('Using the default settings until you save.');
    }

    public function test_first_save_creates_the_settings_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.claim.general.update'), $this->payload(['mileage_enabled' => '1', 'mileage_rate' => '0.6', 'payment_method' => 'separate']))
            ->assertRedirect(route('settings.claim.general'))
            ->assertSessionHas('status', 'Claim settings were saved.');

        $setting = ClaimSetting::query()->sole();
        $this->assertTrue($setting->mileage_enabled);
        $this->assertSame('0.60', $setting->mileage_rate);
        $this->assertSame(ClaimPaymentMethod::Separate, $setting->payment_method);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated claim settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = ClaimSetting::factory()->create();
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.claim.general.update'), $this->payload(['receipt_required_above' => '20']))
            ->assertRedirect(route('settings.claim.general'));

        $this->assertDatabaseCount('claim_settings', 1);
        $this->assertSame('20.00', $setting->fresh()->receipt_required_above);
        $this->assertSame(['receipt_required_above' => '20.00'], Activity::query()->sole()->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.claim.general'))->assertSee('Last changed by');
    }

    public function test_mileage_rate_is_cleared_when_mileage_is_off(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.claim.general.update'), $this->payload(['mileage_enabled' => '0', 'mileage_rate' => '0.6']));

        $this->assertNull(ClaimSetting::query()->sole()->mileage_rate);
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.claim.general'))
            ->put(route('settings.claim.general.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.claim.general'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('claim_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'negative deadline' => [['submission_deadline_days' => '-1'], 'submission_deadline_days'],
            'deadline over a year' => [['submission_deadline_days' => '366'], 'submission_deadline_days'],
            'receipt amount with three decimals' => [['receipt_required_above' => '10.125'], 'receipt_required_above'],
            'mileage without a rate' => [['mileage_enabled' => '1', 'mileage_rate' => ''], 'mileage_rate'],
            'zero mileage rate' => [['mileage_enabled' => '1', 'mileage_rate' => '0'], 'mileage_rate'],
            'unknown payment method' => [['payment_method' => 'cash'], 'payment_method'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'submission_deadline_days' => '30',
            'receipt_required_above' => '0',
            'allow_resubmission' => '1',
            'mileage_enabled' => '0',
            'mileage_rate' => '',
            'payment_method' => 'with-payroll',
        ], $overrides);
    }
}
