<?php

namespace App\Http\Requests\Settings;

use App\Enums\ClaimPaymentMethod;
use App\Enums\Role;
use App\Models\ClaimSetting;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Claim → General.
 */
class UpdateClaimSettingsRequest extends FormRequest
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
            'submission_deadline_days' => ['required', 'integer', 'between:0,'.ClaimSetting::MAX_SUBMISSION_DEADLINE_DAYS],
            'receipt_required_above' => ['required', 'numeric', 'decimal:0,2', 'between:0,10000'],
            'allow_resubmission' => ['required', 'boolean'],
            'mileage_enabled' => ['required', 'boolean'],
            'mileage_rate' => ['exclude_unless:mileage_enabled,1', 'required', 'numeric', 'decimal:0,2', 'between:0.01,10'],
            'payment_method' => ['required', Rule::enum(ClaimPaymentMethod::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'submission_deadline_days' => __('Submission deadline'),
            'receipt_required_above' => __('Receipts needed above'),
            'allow_resubmission' => __('Allow resubmitting rejected claims'),
            'mileage_enabled' => __('Mileage claims'),
            'mileage_rate' => __('Mileage rate'),
            'payment_method' => __('How claims are paid'),
        ];
    }
}
