<?php

namespace App\Http\Requests\Settings;

use App\Enums\LookupCategory;
use App\Enums\Role;
use App\Models\LookupItem;
use App\Rules\AllowedUpload;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates Settings → Company → Basic Details.
 */
class UpdateCompanyDetailsRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:150'],
            'registration_no' => ['nullable', 'regex:/^\d{12}$/'],
            'old_registration_no' => ['nullable', 'regex:/^\d{1,7}-[A-Z]$/'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'website' => ['nullable', 'url', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'digits:5'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', Rule::in(array_keys(LookupItem::options(LookupCategory::MalaysianStates)))],
            'epf_no' => ['nullable', 'string', 'max:30'],
            'socso_no' => ['nullable', 'string', 'max:30'],
            'eis_no' => ['nullable', 'string', 'max:30'],
            'income_tax_no' => ['nullable', 'string', 'max:30'],
            'zakat_no' => ['nullable', 'string', 'max:30'],
            'logo' => ['nullable', 'bail', 'image', new AllowedUpload],
            'remove_logo' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * The company columns to save (everything except the logo fields).
     *
     * @return array<string, mixed>
     */
    public function companyDetails(): array
    {
        return $this->safe()->except(['logo', 'remove_logo']);
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'registration_no.regex' => __('Enter the 12-digit SSM number, e.g. 202301012345.'),
            'old_registration_no.regex' => __('Enter the old SSM number, e.g. 1234567-A.'),
            'phone.regex' => __('Use only numbers, spaces and + - ( ).'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => __('Company name'),
            'registration_no' => __('SSM registration number'),
            'old_registration_no' => __('Old SSM number'),
            'email' => __('Email'),
            'phone' => __('Phone'),
            'website' => __('Website'),
            'address_line_1' => __('Address line 1'),
            'address_line_2' => __('Address line 2'),
            'postcode' => __('Postcode'),
            'city' => __('City'),
            'state' => __('State'),
            'epf_no' => __('EPF employer number'),
            'socso_no' => __('SOCSO employer code'),
            'eis_no' => __('EIS employer code'),
            'income_tax_no' => __('Income tax employer number'),
            'zakat_no' => __('Zakat number'),
            'logo' => __('Logo'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge(array_filter([
            'registration_no' => is_string($this->registration_no) ? preg_replace('/[\s-]/', '', $this->registration_no) : null,
            'old_registration_no' => is_string($this->old_registration_no) ? strtoupper(trim($this->old_registration_no)) : null,
        ], fn (?string $value): bool => $value !== null));
    }
}
