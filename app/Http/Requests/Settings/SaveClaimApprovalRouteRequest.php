<?php

namespace App\Http\Requests\Settings;

use App\Enums\Role;
use App\Models\ClaimApprovalRoute;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates adding (POST) or editing (PUT) a claim approval route. The applicant role is only accepted
 * when adding; a route's role never changes. Each level is {approver: "role:<Role>" or "user:<id>",
 * above: optional RM amount}; level 1 always applies, so it has no amount.
 */
class SaveClaimApprovalRouteRequest extends FormRequest
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
        $rules = [
            'levels' => ['required', 'array', 'max:'.ClaimApprovalRoute::MAX_LEVELS],
            'levels.*.approver' => ['required', 'string', 'distinct', function (string $attribute, mixed $value, Closure $fail): void {
                if (SaveLeaveApprovalRouteRequest::parseLevel((string) $value) === null) {
                    $fail(__('Choose a role or a user that exists.'));
                }
            }],
            'levels.0.above' => ['prohibited'],
            'levels.*.above' => ['nullable', 'numeric', 'decimal:0,2', 'between:0.01,1000000'],
        ];

        if ($this->isMethod('POST')) {
            $rules['applicant_role'] = ['nullable', Rule::enum(Role::class), Rule::unique('claim_approval_routes', 'applicant_role')];
        }

        return $rules;
    }

    /**
     * Only one default route (no applicant role) may exist.
     *
     * @return list<Closure>
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->isMethod('POST') && blank($this->input('applicant_role'))
                    && ClaimApprovalRoute::query()->whereNull('applicant_role')->exists()) {
                    $validator->errors()->add('applicant_role', __('A default route already exists.'));
                }
            },
        ];
    }

    /**
     * The validated levels as stored: [{type: role|user, value: role name|user id, above: amount|null}].
     *
     * @return list<array{type: string, value: string|int, above: string|null}>
     */
    public function levels(): array
    {
        return array_map(fn (array $level): array => [
            ...SaveLeaveApprovalRouteRequest::parseLevel($level['approver']),
            'above' => filled($level['above'] ?? null) ? number_format((float) $level['above'], 2, '.', '') : null,
        ], array_values($this->validated('levels')));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'levels.required' => __('Add at least one approval level.'),
            'levels.max' => __('A route can have up to :max levels.'),
            'levels.*.approver.distinct' => __('Each level needs a different approver.'),
            'levels.0.above.prohibited' => __('Level 1 applies to every claim, so it has no amount.'),
            'applicant_role.unique' => __('This role already has a route.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'applicant_role' => __('Applies to'),
            'levels' => __('Approval levels'),
            'levels.*.approver' => __('Approver'),
            'levels.*.above' => __('Only for claims above'),
        ];
    }

    /**
     * Drop level rows without an approver, so unused rows don't count.
     */
    protected function prepareForValidation(): void
    {
        $levels = is_array($this->input('levels')) ? $this->input('levels') : [];

        $this->merge(['levels' => array_values(array_filter(
            $levels,
            fn ($level): bool => is_array($level) && filled($level['approver'] ?? null),
        ))]);
    }
}
