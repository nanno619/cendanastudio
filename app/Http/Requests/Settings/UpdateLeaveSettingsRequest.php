<?php

namespace App\Http\Requests\Settings;

use App\Enums\LeaveRounding;
use App\Enums\LookupCategory;
use App\Enums\Role;
use App\Enums\UnusedLeaveTreatment;
use App\Enums\Weekday;
use App\Models\LookupItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Leave → General.
 */
class UpdateLeaveSettingsRequest extends FormRequest
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
        $workingDays = is_array($this->input('working_days')) ? $this->input('working_days') : [];

        return [
            'leave_year_start_month' => ['required', 'integer', 'between:1,12'],
            'working_days' => ['required', 'array', 'min:1'],
            'working_days.*' => ['required', 'distinct', Rule::enum(Weekday::class)],
            'half_working_day' => ['nullable', Rule::enum(Weekday::class), Rule::in($workingDays)],
            'public_holiday_state' => ['nullable', Rule::in(array_keys(LookupItem::options(LookupCategory::MalaysianStates)))],
            'allow_half_day' => ['required', 'boolean'],
            'annual_leave_notice_days' => ['required', 'integer', 'between:0,90'],
            'backdate_days' => ['required', 'integer', 'between:0,90'],
            'allow_leave_during_probation' => ['required', 'boolean'],
            'allow_negative_balance' => ['required', 'boolean'],
            'max_negative_days' => ['exclude_unless:allow_negative_balance,1', 'required', 'numeric', 'between:0.5,30', 'multiple_of:0.5'],
            'prorate_entitlement' => ['required', 'boolean'],
            'proration_rounding' => ['required', Rule::enum(LeaveRounding::class)],
            'resignation_unused_leave' => ['required', Rule::enum(UnusedLeaveTreatment::class)],
            'share_leave_calendar' => ['required', 'boolean'],
            'hr_notification_email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'half_working_day.in' => __('The half working day must be one of the working days.'),
            'max_negative_days.multiple_of' => __('Use whole or half days, e.g. 2.5.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'leave_year_start_month' => __('Leave year starts in'),
            'working_days' => __('Working days'),
            'working_days.*' => __('Working days'),
            'half_working_day' => __('Half working day'),
            'public_holiday_state' => __('Public holidays follow'),
            'allow_half_day' => __('Allow half-day leave'),
            'annual_leave_notice_days' => __('Annual leave notice'),
            'backdate_days' => __('Backdated applications'),
            'allow_leave_during_probation' => __('Annual leave during probation'),
            'allow_negative_balance' => __('Allow negative balance'),
            'max_negative_days' => __('Days staff can borrow'),
            'prorate_entitlement' => __('Prorate for joiners and leavers'),
            'proration_rounding' => __('Rounding'),
            'resignation_unused_leave' => __('Unused leave on resignation'),
            'share_leave_calendar' => __('Shared leave calendar'),
            'hr_notification_email' => __('HR copy email'),
        ];
    }
}
