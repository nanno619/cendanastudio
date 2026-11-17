<?php

namespace App\Http\Requests\Settings;

use App\Enums\OvertimeCapture;
use App\Enums\OvertimeRounding;
use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Hadir → Overtime.
 */
class UpdateAttendanceOvertimeSettingsRequest extends FormRequest
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
            'capture_method' => ['required', Rule::enum(OvertimeCapture::class)],
            'minimum_minutes' => ['required', 'integer', 'between:0,120'],
            'rounding' => ['required', Rule::enum(OvertimeRounding::class)],
            'allow_on_rest_days' => ['required', 'boolean'],
            'approver_role' => ['required', Rule::enum(Role::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'capture_method' => __('How overtime is recorded'),
            'minimum_minutes' => __('Minimum overtime'),
            'rounding' => __('Rounding'),
            'allow_on_rest_days' => __('Overtime on rest days and public holidays'),
            'approver_role' => __('Approved by'),
        ];
    }
}
