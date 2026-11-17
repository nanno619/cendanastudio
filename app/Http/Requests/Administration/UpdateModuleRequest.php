<?php

namespace App\Http\Requests\Administration;

use App\Enums\ModuleKey;
use App\Enums\Role;
use App\Models\Module;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Administrations → Modules → Edit. Names are unique among modules; no roles chosen means
 * everyone can use the module. Super Admin always can, so it isn't offered.
 */
class UpdateModuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasRole(Role::SuperAdmin) ?? false;
    }

    /**
     * Blank Malay names are saved as null, so the English name is used.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'name_en' => trim((string) $this->input('name_en')),
            'name_ms' => filled($this->input('name_ms')) ? trim((string) $this->input('name_ms')) : null,
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var ModuleKey $key */
        $key = $this->route('moduleKey');
        $unique = fn (string $column) => Rule::unique('modules', $column)->where(fn ($query) => $query->where('key', '!=', $key->value));

        return [
            'name_en' => ['required', 'string', 'max:'.Module::MAX_NAME_LENGTH, $unique('name_en')],
            'name_ms' => ['nullable', 'string', 'max:'.Module::MAX_NAME_LENGTH],
            'icon' => ['required', Rule::in(Module::ICONS)],
            'sort_order' => ['required', 'integer', 'between:1,99'],
            'is_enabled' => ['required', 'boolean'],
            'allowed_roles' => ['array'],
            'allowed_roles.*' => ['required', 'distinct', Rule::enum(Role::class)->except([Role::SuperAdmin])],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name_en.unique' => __('Another module already uses this name.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name_en' => __('Name (English)'),
            'name_ms' => __('Name (Bahasa Melayu)'),
            'icon' => __('Icon'),
            'sort_order' => __('Display order'),
            'is_enabled' => __('Module is on'),
            'allowed_roles' => __('Who can use it'),
            'allowed_roles.*' => __('Who can use it'),
        ];
    }
}
