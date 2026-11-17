<?php

namespace App\Http\Requests\Settings;

use App\Enums\ProRataBasis;
use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Payroll → Pro-Rata. The fixed number of days is needed only when a basis uses it.
 */
class UpdateProRataSettingsRequest extends FormRequest
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
        $fixed = ProRataBasis::FixedDays->value;

        return [
            'joiner_leaver_basis' => ['required', Rule::enum(ProRataBasis::class)],
            'unpaid_leave_basis' => ['required', Rule::enum(ProRataBasis::class)],
            'fixed_days' => [
                Rule::excludeIf(! in_array($fixed, [$this->input('joiner_leaver_basis'), $this->input('unpaid_leave_basis')], true)),
                'required', 'integer', 'between:20,31',
            ],
            'prorate_fixed_allowances' => ['required', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'joiner_leaver_basis' => __('Joiners and leavers'),
            'unpaid_leave_basis' => __('Unpaid leave and absence'),
            'fixed_days' => __('Fixed number of days'),
            'prorate_fixed_allowances' => __('Prorate fixed allowances'),
        ];
    }
}
