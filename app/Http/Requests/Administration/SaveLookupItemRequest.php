<?php

namespace App\Http\Requests\Administration;

use App\Enums\LookupCategory;
use App\Enums\Role;
use App\Models\LookupItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates adding (POST) or editing (PUT) a Data Dictionary item. The code is only accepted
 * when adding: records store it, so it never changes afterwards.
 */
class SaveLookupItemRequest extends FormRequest
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
        /** @var LookupCategory $category */
        $category = $this->route('category');
        $item = $this->route('item');
        $inCategory = fn ($query) => $query->where('category', $category->value);

        $rules = [
            'label_en' => ['required', 'string', 'max:255', Rule::unique('lookup_items')->where($inCategory)->ignore($item)],
            'label_ms' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'tone' => ['nullable', Rule::in(LookupItem::TONES)],
            'is_active' => ['required', 'boolean'],
        ];

        if (! $item) {
            $rules['code'] = ['required', 'string', 'max:50', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('lookup_items')->where($inCategory)];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'code.regex' => __('Use lowercase letters, numbers and single hyphens only, e.g. negeri-sembilan.'),
            'code.unique' => __('This category already has an item with this code.'),
            'label_en.unique' => __('This category already has an item with this English label.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'code' => __('Code'),
            'label_en' => __('Label (English)'),
            'label_ms' => __('Label (Malay)'),
            'sort_order' => __('Display order'),
            'tone' => __('Badge colour'),
        ];
    }
}
