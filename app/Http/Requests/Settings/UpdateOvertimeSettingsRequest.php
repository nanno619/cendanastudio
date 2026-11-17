<?php

namespace App\Http\Requests\Settings;

use App\Enums\OvertimeEligibility;
use App\Enums\Role;
use App\Models\OvertimeSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Payroll → Overtime. Rates can't go below the Employment Act minimums.
 */
class UpdateOvertimeSettingsRequest extends FormRequest
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
        $rate = fn (float $minimum): array => ['required', 'numeric', 'decimal:0,2', 'min:'.$minimum, 'max:10'];

        return [
            'eligibility' => ['required', Rule::enum(OvertimeEligibility::class)],
            'eligibility_wage_limit' => ['exclude_unless:eligibility,'.OvertimeEligibility::EmploymentAct->value, 'required', 'numeric', 'decimal:0,2', 'between:1,100000'],
            'normal_day_rate' => $rate(OvertimeSetting::MIN_NORMAL_DAY_RATE),
            'rest_day_rate' => $rate(OvertimeSetting::MIN_REST_DAY_RATE),
            'public_holiday_rate' => $rate(OvertimeSetting::MIN_PUBLIC_HOLIDAY_RATE),
            'normal_hours_per_day' => ['required', 'numeric', 'multiple_of:0.5', 'between:1,10'],
            'monthly_limit_hours' => ['required', 'integer', 'between:1,'.OvertimeSetting::MAX_MONTHLY_HOURS],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        $belowMinimum = __('The :attribute can’t be lower than the Employment Act minimum of :min×.');

        return [
            'normal_day_rate.min' => $belowMinimum,
            'rest_day_rate.min' => $belowMinimum,
            'public_holiday_rate.min' => $belowMinimum,
            'monthly_limit_hours.between' => __('The monthly limit must be between :min and :max hours, the legal maximum.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'eligibility' => __('Who gets overtime pay'),
            'eligibility_wage_limit' => __('Wage limit'),
            'normal_day_rate' => __('normal working day rate'),
            'rest_day_rate' => __('rest day rate'),
            'public_holiday_rate' => __('public holiday rate'),
            'normal_hours_per_day' => __('Normal hours per day'),
            'monthly_limit_hours' => __('Monthly limit'),
        ];
    }
}
