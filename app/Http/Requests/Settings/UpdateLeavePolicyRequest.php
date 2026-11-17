<?php

namespace App\Http\Requests\Settings;

use App\Enums\Role;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates one leave type's policy on Settings → Leave → Leave Policies.
 */
class UpdateLeavePolicyRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    /**
     * @return array<string, ValidationRule|Closure|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'entitlements' => ['present', 'array', 'max:10', function (string $attribute, mixed $value, Closure $fail): void {
                if (is_array($value) && $value !== [] && ! in_array('0', array_map(fn ($tier): string => (string) ($tier['min_years'] ?? ''), $value), true)) {
                    $fail(__('The first tier must start from 0 years of service.'));
                }
            }],
            'entitlements.*.min_years' => ['required', 'integer', 'between:0,50', 'distinct'],
            'entitlements.*.days' => ['required', 'numeric', 'between:0.5,365', 'multiple_of:0.5'],
            'is_paid' => ['required', 'boolean'],
            'requires_attachment' => ['required', 'boolean'],
            'allow_carry_forward' => ['required', 'boolean'],
            'carry_forward_max_days' => ['exclude_unless:allow_carry_forward,1', 'required', 'numeric', 'between:0.5,365', 'multiple_of:0.5'],
            'carry_forward_expiry_months' => ['exclude_unless:allow_carry_forward,1', 'nullable', 'integer', 'between:1,12'],
        ];
    }

    /**
     * Entitlement tiers sorted by years of service, with typed numbers.
     *
     * @return list<array{min_years: int, days: float|int}>
     */
    public function entitlements(): array
    {
        return collect($this->validated('entitlements'))
            ->map(fn (array $tier): array => ['min_years' => (int) $tier['min_years'], 'days' => $tier['days'] + 0])
            ->sortBy('min_years')
            ->values()
            ->all();
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'entitlements.*.min_years.distinct' => __('Each tier needs different years of service.'),
            'entitlements.*.days.multiple_of' => __('Use whole or half days, e.g. 2.5.'),
            'carry_forward_max_days.multiple_of' => __('Use whole or half days, e.g. 2.5.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'entitlements' => __('Entitlement'),
            'entitlements.*.min_years' => __('Years of service'),
            'entitlements.*.days' => __('Days'),
            'is_paid' => __('Paid leave'),
            'requires_attachment' => __('Attachment required'),
            'allow_carry_forward' => __('Allow carry forward'),
            'carry_forward_max_days' => __('Maximum days carried forward'),
            'carry_forward_expiry_months' => __('Carried days expire after'),
        ];
    }

    /**
     * Drop tier rows left completely blank, so unused rows don't fail validation.
     */
    protected function prepareForValidation(): void
    {
        $tiers = is_array($this->input('entitlements')) ? $this->input('entitlements') : [];

        $this->merge(['entitlements' => array_values(array_filter(
            $tiers,
            fn ($tier): bool => is_array($tier) && (filled($tier['min_years'] ?? null) || filled($tier['days'] ?? null)),
        ))]);
    }
}
