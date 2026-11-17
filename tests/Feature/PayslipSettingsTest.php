<?php

namespace Tests\Feature;

use App\Enums\PayslipIdentityDisplay;
use App\Enums\Role as RoleName;
use App\Models\Company;
use App\Models\PayslipSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Activitylog\Models\Activity;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class PayslipSettingsTest extends TestCase
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
        $default = PayslipSetting::current();
        $this->assertFalse($default->exists);
        $this->assertSame(PayslipIdentityDisplay::Masked, $default->identity_display);
        $this->assertTrue($default->mask_bank_account);
        $this->assertFalse($default->show_leave_balance);
        $this->assertFalse($default->password_protect);
        $this->assertSame('en', $default->language);

        $saved = PayslipSetting::factory()->create(['language' => 'ms']);
        $this->assertTrue(PayslipSetting::current()->is($saved));
        $this->assertSame('ms', PayslipSetting::current()->language);
    }

    public function test_settings_are_identified_by_ulid_and_keep_an_integer_id(): void
    {
        $setting = PayslipSetting::factory()->create();

        $this->assertIsInt($setting->id);
        $this->assertTrue(Str::isUlid($setting->ulid));
        $this->assertSame($setting->ulid, $setting->getRouteKey());
    }

    public function test_identity_and_bank_account_are_shown_as_configured(): void
    {
        $setting = new PayslipSetting(['identity_display' => PayslipIdentityDisplay::Masked, 'mask_bank_account' => true]);
        $this->assertSame('XXXXXX-XX-5678', $setting->displayIdentity('900101-14-5678'));
        $this->assertSame('XXXXX6789', $setting->displayIdentity('A12346789'));
        $this->assertSame('********8901', $setting->displayBankAccount('512345678901'));

        $setting->fill(['identity_display' => PayslipIdentityDisplay::Full, 'mask_bank_account' => false]);
        $this->assertSame('900101-14-5678', $setting->displayIdentity('900101-14-5678'));
        $this->assertSame('512345678901', $setting->displayBankAccount('512345678901'));

        $setting->identity_display = PayslipIdentityDisplay::Hidden;
        $this->assertNull($setting->displayIdentity('900101-14-5678'));
    }

    public function test_guests_are_redirected_to_sign_in(): void
    {
        $this->get(route('settings.payroll.payslip'))->assertRedirect(route('login'));
        $this->get(route('settings.payroll.payslip.preview'))->assertRedirect(route('login'));
    }

    public function test_other_roles_are_forbidden_and_do_not_see_the_menu_link(): void
    {
        $admin = User::factory()->create()->assignRole(RoleName::Admin);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertDontSee(route('settings.payroll.payslip'));
        $this->actingAs($admin)->get(route('settings.payroll.payslip'))->assertForbidden();
        $this->actingAs($admin)->put(route('settings.payroll.payslip.update'), $this->payload())->assertForbidden();
        $this->actingAs($admin)->get(route('settings.payroll.payslip.preview'))->assertForbidden();
    }

    public function test_super_admin_sees_the_defaults_and_the_preview_link(): void
    {
        $this->actingAs($this->superAdmin)->get(route('admin.dashboard'))->assertSee(route('settings.payroll.payslip'));

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.payslip'))
            ->assertOk()
            ->assertSee('name="identity_display"', false)
            ->assertSee('value="masked" selected', false)
            ->assertSee(route('settings.payroll.payslip.preview'))
            ->assertSee('Using the default settings until you save.');
    }

    public function test_preview_streams_a_pdf_before_and_after_the_company_is_saved(): void
    {
        $this->actingAs($this->superAdmin)->get(route('settings.payroll.payslip.preview'))
            ->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        Company::factory()->create();
        PayslipSetting::factory()->create(['language' => 'ms', 'show_leave_balance' => true, 'footer_note' => "Line one\nLine two"]);

        $response = $this->actingAs($this->superAdmin)->get(route('settings.payroll.payslip.preview'))->assertOk();
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_first_save_creates_the_settings_and_is_audited(): void
    {
        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.payslip.update'), $this->payload(['identity_display' => 'hidden', 'footer_note' => '  Questions? Email hr@example.com  ']))
            ->assertRedirect(route('settings.payroll.payslip'))
            ->assertSessionHas('status', 'Payslip settings were saved.');

        $setting = PayslipSetting::query()->sole();
        $this->assertSame(PayslipIdentityDisplay::Hidden, $setting->identity_display);
        $this->assertSame('Questions? Email hr@example.com', $setting->footer_note);

        $activity = Activity::query()->whereMorphedTo('subject', $setting)->sole();
        $this->assertSame('Updated payslip settings', $activity->description);
        $this->assertTrue($activity->causer->is($this->superAdmin));
    }

    public function test_saving_again_updates_the_same_row_and_audits_only_the_changes(): void
    {
        $setting = PayslipSetting::factory()->create(['footer_note' => 'Old note']);
        Activity::query()->delete();

        $this->actingAs($this->superAdmin)
            ->put(route('settings.payroll.payslip.update'), $this->payload(['show_leave_balance' => '1', 'footer_note' => '   ']))
            ->assertRedirect(route('settings.payroll.payslip'));

        $this->assertDatabaseCount('payslip_settings', 1);
        $setting->refresh();
        $this->assertTrue($setting->show_leave_balance);
        $this->assertNull($setting->footer_note);
        $this->assertSame(['show_leave_balance' => true, 'footer_note' => null], Activity::query()->sole()->attribute_changes['attributes']);

        $this->actingAs($this->superAdmin)->get(route('settings.payroll.payslip'))->assertSee('Last changed by');
    }

    /**
     * @param  array<string, mixed>  $overrides
     */
    #[DataProvider('invalidSettings')]
    public function test_invalid_settings_are_rejected(array $overrides, string $field): void
    {
        $this->actingAs($this->superAdmin)
            ->from(route('settings.payroll.payslip'))
            ->put(route('settings.payroll.payslip.update'), $this->payload($overrides))
            ->assertRedirect(route('settings.payroll.payslip'))
            ->assertSessionHasErrors($field);

        $this->assertDatabaseCount('payslip_settings', 0);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public static function invalidSettings(): array
    {
        return [
            'unknown identity display' => [['identity_display' => 'partial'], 'identity_display'],
            'unknown language' => [['language' => 'id'], 'language'],
            'footer note too long' => [['footer_note' => str_repeat('a', 501)], 'footer_note'],
            'missing switch' => [['show_year_to_date' => null], 'show_year_to_date'],
            'switch not a boolean' => [['password_protect' => 'maybe'], 'password_protect'],
        ];
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function payload(array $overrides = []): array
    {
        return array_merge([
            'show_company_logo' => '1',
            'show_employer_numbers' => '1',
            'identity_display' => 'masked',
            'mask_bank_account' => '1',
            'show_employer_contributions' => '1',
            'show_year_to_date' => '1',
            'show_leave_balance' => '0',
            'language' => 'en',
            'footer_note' => '',
            'email_on_release' => '1',
            'password_protect' => '0',
        ], $overrides);
    }
}
