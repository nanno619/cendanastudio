<?php

namespace App\Http\Requests\Projects;

use App\Enums\LookupCategory;
use App\Models\Client;
use App\Models\LookupItem;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates adding (POST) or editing (PUT) a client and its contact persons. Contact rows are fixed on the
 * form; untouched rows are dropped but keep their row index so errors line up.
 */
class SaveClientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null && Client::isEditableBy($this->user());
    }

    public function client(): ?Client
    {
        $client = $this->route('client');

        return $client instanceof Client ? $client : null;
    }

    /**
     * SSM numbers lose spaces and dashes; contact rows with nothing typed in are dropped.
     */
    protected function prepareForValidation(): void
    {
        $rows = is_array($this->input('contacts')) ? $this->input('contacts') : [];

        $this->merge([
            'registration_no' => filled($this->input('registration_no')) ? preg_replace('/[\s-]/', '', (string) $this->input('registration_no')) : null,
            'email' => filled($this->input('email')) ? strtolower(trim((string) $this->input('email'))) : null,
            'contacts' => array_filter(
                array_map(fn ($row): array => is_array($row) ? array_map(fn ($value): ?string => filled($value) ? trim((string) $value) : null, $row) : [], $rows),
                fn (array $row): bool => collect($row)->filter()->isNotEmpty(),
            ),
        ]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $phone = ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'];

        return [
            'name' => ['required', 'string', 'max:150'],
            'registration_no' => ['nullable', 'regex:/^\d{12}$/', Rule::unique('clients', 'registration_no')->ignore($this->client()?->id)],
            'phone' => $phone,
            'email' => ['nullable', 'email', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'digits:5'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', Rule::in(array_filter([...array_keys(LookupItem::options(LookupCategory::MalaysianStates)), $this->client()?->state]))],
            'notes' => ['nullable', 'string', 'max:2000'],
            'contacts' => ['array', 'max:'.Client::MAX_CONTACTS],
            'contacts.*.name' => ['required', 'string', 'max:150'],
            'contacts.*.position' => ['nullable', 'string', 'max:100'],
            'contacts.*.phone' => $phone,
            'contacts.*.email' => ['nullable', 'email', 'max:255'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function clientAttributes(): array
    {
        return collect($this->validated())->except('contacts')->all();
    }

    /**
     * The contact persons in form order.
     *
     * @return list<array{name: string, position: string|null, phone: string|null, email: string|null}>
     */
    public function contacts(): array
    {
        $contacts = $this->validated('contacts') ?? [];
        ksort($contacts);

        return array_values(array_map(fn (array $contact): array => [
            'name' => $contact['name'],
            'position' => $contact['position'] ?? null,
            'phone' => $contact['phone'] ?? null,
            'email' => isset($contact['email']) ? strtolower($contact['email']) : null,
        ], $contacts));
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'registration_no.regex' => __('Enter the 12-digit SSM number, e.g. 202301012345.'),
            'phone.regex' => __('Use only numbers, spaces and + - ( ).'),
            'contacts.*.phone.regex' => __('Use only numbers, spaces and + - ( ).'),
            'contacts.max' => __('Add up to :max contact persons.'),
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
            'phone' => __('Phone'),
            'email' => __('Email'),
            'address_line_1' => __('Address line 1'),
            'address_line_2' => __('Address line 2'),
            'postcode' => __('Postcode'),
            'city' => __('City'),
            'state' => __('State'),
            'notes' => __('Notes'),
            'contacts.*.name' => __('Contact name'),
            'contacts.*.position' => __('Position'),
            'contacts.*.phone' => __('Contact phone'),
            'contacts.*.email' => __('Contact email'),
        ];
    }
}
