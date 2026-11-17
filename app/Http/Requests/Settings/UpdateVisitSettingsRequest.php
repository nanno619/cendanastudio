<?php

namespace App\Http\Requests\Settings;

use App\Enums\Role;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Hadir → Visits. At least one role must be allowed while visits are on, and the
 * approver role is needed only while approval is on.
 */
class UpdateVisitSettingsRequest extends FormRequest
{
    /**
     * Switches on the form, in the order they appear.
     *
     * @var list<string>
     */
    public const array SWITCHES = [
        'visits_enabled', 'counts_as_attendance', 'require_client', 'require_purpose', 'require_photo',
        'requires_approval', 'suggest_mileage_claim',
    ];

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
            ...array_fill_keys(self::SWITCHES, ['required', 'boolean']),
            'allowed_roles' => ['array', 'required_if_accepted:visits_enabled'],
            'allowed_roles.*' => ['required', 'distinct', Rule::enum(Role::class)],
            'approver_role' => ['exclude_unless:requires_approval,1', 'required', Rule::enum(Role::class)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'allowed_roles.required_if_accepted' => __('Choose at least one role while visits are on.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'visits_enabled' => __('Visits'),
            'allowed_roles' => __('Who can log visits'),
            'allowed_roles.*' => __('Who can log visits'),
            'counts_as_attendance' => __('Counts as attendance'),
            'require_client' => __('Client or site name'),
            'require_purpose' => __('Purpose'),
            'require_photo' => __('Photo at check-in'),
            'requires_approval' => __('Visits need approval'),
            'approver_role' => __('Approved by'),
            'suggest_mileage_claim' => __('Offer a mileage claim'),
        ];
    }
}
