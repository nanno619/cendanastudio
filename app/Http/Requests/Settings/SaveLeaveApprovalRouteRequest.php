<?php

namespace App\Http\Requests\Settings;

use App\Enums\Role;
use App\Models\LeaveApprovalRoute;
use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

/**
 * Validates adding (POST) or editing (PUT) a leave approval route. The applicant role is only
 * accepted when adding; a route's role never changes. Each level is "role:<Role>" or "user:<id>".
 */
class SaveLeaveApprovalRouteRequest extends FormRequest
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
            'levels' => ['required', 'array', 'max:'.LeaveApprovalRoute::MAX_LEVELS],
            'levels.*' => ['required', 'string', 'distinct', function (string $attribute, mixed $value, Closure $fail): void {
                if (static::parseLevel((string) $value) === null) {
                    $fail(__('Choose a role or a user that exists.'));
                }
            }],
        ];

        if ($this->isMethod('POST')) {
            $rules['applicant_role'] = ['nullable', Rule::enum(Role::class), Rule::unique('leave_approval_routes', 'applicant_role')];
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
                    && LeaveApprovalRoute::query()->whereNull('applicant_role')->exists()) {
                    $validator->errors()->add('applicant_role', __('A default route already exists.'));
                }
            },
        ];
    }

    /**
     * The validated levels as stored: [{type: role|user, value: role name|user id}].
     *
     * @return list<array{type: string, value: string|int}>
     */
    public function levels(): array
    {
        return array_values(array_map(fn (string $level): array => static::parseLevel($level), $this->validated('levels')));
    }

    /**
     * Turn "role:Director" or "user:12" into a level, or null when the role or user doesn't exist.
     *
     * @return array{type: string, value: string|int}|null
     */
    public static function parseLevel(string $level): ?array
    {
        [$type, $value] = array_pad(explode(':', $level, 2), 2, '');

        return match (true) {
            $type === 'role' && Role::tryFrom($value) !== null => ['type' => 'role', 'value' => $value],
            $type === 'user' && ctype_digit($value) && User::query()->whereKey((int) $value)->exists() => ['type' => 'user', 'value' => (int) $value],
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'levels.required' => __('Add at least one approval level.'),
            'levels.max' => __('A route can have up to :max levels.'),
            'levels.*.distinct' => __('Each level needs a different approver.'),
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
            'levels.*' => __('Approver'),
        ];
    }

    /**
     * Drop level rows left blank, so unused rows don't count.
     */
    protected function prepareForValidation(): void
    {
        $levels = is_array($this->input('levels')) ? $this->input('levels') : [];

        $this->merge(['levels' => array_values(array_filter($levels, fn ($level): bool => filled($level)))]);
    }
}
