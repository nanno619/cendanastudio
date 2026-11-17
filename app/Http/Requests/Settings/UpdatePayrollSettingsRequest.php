<?php

namespace App\Http\Requests\Settings;

use App\Enums\HrdfLevy;
use App\Enums\NetPayRounding;
use App\Enums\PayDayAdjustment;
use App\Enums\PayDayRule;
use App\Enums\PaymentMethod;
use App\Enums\PcbMethod;
use App\Enums\Role;
use App\Models\PayrollSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Payroll → General.
 */
class UpdatePayrollSettingsRequest extends FormRequest
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
        $latestPayDay = $this->input('pay_day_rule') === PayDayRule::NextMonthDay->value ? PayrollSetting::LATEST_PAY_DAY_NEXT_MONTH : 31;

        return [
            'pay_day_rule' => ['required', Rule::enum(PayDayRule::class)],
            'pay_day' => ['exclude_if:pay_day_rule,'.PayDayRule::LastWorkingDay->value, 'required', 'integer', 'between:1,'.$latestPayDay],
            'pay_day_adjustment' => ['required', Rule::enum(PayDayAdjustment::class)],
            'cutoff_day' => ['required', 'integer', 'between:1,31'],
            'socso_enabled' => ['required', 'boolean'],
            'eis_enabled' => ['required', 'boolean'],
            'pcb_enabled' => ['required', 'boolean'],
            'pcb_method' => ['required', Rule::enum(PcbMethod::class)],
            'hrdf_levy' => ['required', Rule::enum(HrdfLevy::class)],
            'zakat_via_payroll' => ['required', 'boolean'],
            'payment_method' => ['required', Rule::enum(PaymentMethod::class)],
            'net_pay_rounding' => ['required', Rule::enum(NetPayRounding::class)],
            'requires_approval' => ['required', 'boolean'],
            'approver_role' => ['exclude_unless:requires_approval,1', 'required', Rule::enum(Role::class)],
            'lock_after_release' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'pay_day.between' => $this->input('pay_day_rule') === PayDayRule::NextMonthDay->value
                ? __('Pay by the 7th of the following month at the latest (Employment Act).')
                : __('Enter a day from 1 to 31.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'pay_day_rule' => __('Pay day'),
            'pay_day' => __('Day of the month'),
            'pay_day_adjustment' => __('If pay day is a rest day or public holiday'),
            'cutoff_day' => __('Cut-off day'),
            'socso_enabled' => __('SOCSO'),
            'eis_enabled' => __('EIS'),
            'pcb_enabled' => __('PCB'),
            'pcb_method' => __('PCB method'),
            'hrdf_levy' => __('HRDF levy'),
            'zakat_via_payroll' => __('Zakat through payroll'),
            'payment_method' => __('Default payment method'),
            'net_pay_rounding' => __('Net pay rounding'),
            'requires_approval' => __('Payroll needs approval'),
            'approver_role' => __('Approved by'),
            'lock_after_release' => __('Lock after release'),
        ];
    }
}
