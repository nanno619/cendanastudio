<?php

namespace App\Http\Requests\Settings;

use App\Enums\PayslipIdentityDisplay;
use App\Enums\Role;
use App\Models\PayslipSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Payroll → Payslip.
 */
class UpdatePayslipSettingsRequest extends FormRequest
{
    /**
     * Switches on the form, in the order they appear.
     *
     * @var list<string>
     */
    public const array SWITCHES = [
        'show_company_logo', 'show_employer_numbers', 'mask_bank_account', 'show_employer_contributions',
        'show_year_to_date', 'show_leave_balance', 'email_on_release', 'password_protect',
    ];

    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    /**
     * A blank footer note is saved as null.
     */
    protected function prepareForValidation(): void
    {
        $this->merge(['footer_note' => filled($this->input('footer_note')) ? trim((string) $this->input('footer_note')) : null]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            ...array_fill_keys(self::SWITCHES, ['required', 'boolean']),
            'identity_display' => ['required', Rule::enum(PayslipIdentityDisplay::class)],
            'language' => ['required', Rule::in(array_keys(config('cendana.locales')))],
            'footer_note' => ['nullable', 'string', 'max:'.PayslipSetting::FOOTER_NOTE_MAX_LENGTH],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'show_company_logo' => __('Company logo'),
            'show_employer_numbers' => __('Employer numbers'),
            'identity_display' => __('IC or passport number'),
            'mask_bank_account' => __('Mask bank account number'),
            'show_employer_contributions' => __('Employer contributions'),
            'show_year_to_date' => __('Year-to-date totals'),
            'show_leave_balance' => __('Leave balance'),
            'language' => __('Payslip language'),
            'footer_note' => __('Footer note'),
            'email_on_release' => __('Email payslips on release'),
            'password_protect' => __('Password-protect emailed payslips'),
        ];
    }
}
