<?php

namespace App\Http\Requests\Settings;

use App\Enums\LookupCategory;
use App\Enums\Role;
use App\Models\LookupItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Company → Bank Account Details.
 */
class UpdateCompanyBankAccountDetailsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'bank' => ['nullable', Rule::in(array_keys(LookupItem::options(LookupCategory::Banks)))],
            'bank_account_holder' => ['nullable', 'string', 'max:150'],
            'bank_account_no' => ['nullable', 'regex:/^\d{5,20}$/'],
            'epf_no' => ['nullable', 'string', 'max:30'],
            'epf_foreign_no' => ['nullable', 'string', 'max:30'],
            'socso_no' => ['nullable', 'string', 'max:30'],
            'eis_no' => ['nullable', 'string', 'max:30'],
            'income_tax_no' => ['nullable', 'string', 'max:30'],
            'zakat_no' => ['nullable', 'string', 'max:30'],
            'hrdf_no' => ['nullable', 'string', 'max:30'],
            'ptptn_no' => ['nullable', 'string', 'max:30'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'bank_account_no.regex' => __('Enter 5 to 20 digits, e.g. 514123456789.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'bank' => __('Bank'),
            'bank_account_holder' => __('Account holder name'),
            'bank_account_no' => __('Account number'),
            'epf_no' => __('EPF employer number (Malaysian)'),
            'epf_foreign_no' => __('EPF employer number (Non-Malaysian)'),
            'socso_no' => __('SOCSO employer code'),
            'eis_no' => __('EIS employer code'),
            'income_tax_no' => __('LHDN employer number'),
            'zakat_no' => __('Zakat number'),
            'hrdf_no' => __('HRDF account number'),
            'ptptn_no' => __('PTPTN employer code'),
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->bank_account_no)) {
            $this->merge(['bank_account_no' => preg_replace('/[\s-]/', '', $this->bank_account_no)]);
        }
    }
}
