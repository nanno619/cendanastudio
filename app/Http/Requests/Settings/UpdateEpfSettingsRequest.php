<?php

namespace App\Http\Requests\Settings;

use App\Enums\EpfCalculationMethod;
use App\Enums\Role;
use App\Models\EpfSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Payroll → EPF. Rates are percentages with up to 2 decimals.
 */
class UpdateEpfSettingsRequest extends FormRequest
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
        $rate = ['required', 'numeric', 'decimal:0,2', 'between:0,30'];

        return [
            ...array_fill_keys(EpfSetting::RATE_FIELDS, $rate),
            'wage_threshold' => ['required', 'numeric', 'decimal:0,2', 'between:1,100000'],
            'calculation_method' => ['required', Rule::enum(EpfCalculationMethod::class)],
            'allow_employer_top_up' => ['required', 'boolean'],
            'employer_top_up_rate' => ['exclude_unless:allow_employer_top_up,1', 'required', 'numeric', 'decimal:0,2', 'max:30', 'gt:malaysian_employer_rate'],
            'allow_employee_elected_rate' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'employer_top_up_rate.gt' => __('The top-up rate must be higher than the statutory employer rate.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'malaysian_employee_rate' => __('Employee rate (Malaysian, under 60)'),
            'malaysian_employer_rate' => __('Employer rate up to the threshold'),
            'malaysian_employer_rate_above_threshold' => __('Employer rate above the threshold'),
            'wage_threshold' => __('Wage threshold'),
            'senior_employee_rate' => __('Employee rate (60 and over)'),
            'senior_employer_rate' => __('Employer rate (60 and over)'),
            'foreign_employee_rate' => __('Employee rate (non-Malaysian)'),
            'foreign_employer_rate' => __('Employer rate (non-Malaysian)'),
            'calculation_method' => __('Calculation method'),
            'allow_employer_top_up' => __('Employer top-up'),
            'employer_top_up_rate' => __('Top-up employer rate'),
            'allow_employee_elected_rate' => __('Employees can elect a higher rate'),
        ];
    }
}
