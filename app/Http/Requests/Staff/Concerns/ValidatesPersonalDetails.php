<?php

namespace App\Http\Requests\Staff\Concerns;

use App\Enums\LookupCategory;
use App\Models\Employee;
use App\Models\LookupItem;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\In;

/**
 * Personal details and emergency contacts, validated the same way when HR edits a staff record and when
 * staff edit their own (My profile). Lookups accept active codes plus the code already stored on the
 * record, so a since-deactivated value can be kept.
 */
trait ValidatesPersonalDetails
{
    /**
     * The record being changed, or null when adding one.
     */
    abstract protected function personalRecord(): ?Employee;

    /**
     * @return array<string, array<mixed>>
     */
    protected function personalRules(bool $childrenRequired = true): array
    {
        return [
            'gender' => ['nullable', $this->lookup(LookupCategory::Gender, 'gender')],
            'race' => ['nullable', $this->lookup(LookupCategory::Race, 'race')],
            'marital_status' => ['nullable', $this->lookup(LookupCategory::MaritalStatus, 'marital_status')],
            'children_count' => [$childrenRequired ? 'required' : 'sometimes', 'required', 'integer', 'between:0,20'],
            'spouse_working' => ['nullable', 'boolean'],
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\+?[0-9][0-9 -]{6,18}$/'],
            'personal_email' => ['nullable', 'email', 'max:255'],
            'address_line_1' => ['nullable', 'string', 'max:255'],
            'address_line_2' => ['nullable', 'string', 'max:255'],
            'postcode' => ['nullable', 'digits:5'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', $this->lookup(LookupCategory::MalaysianStates, 'state')],
        ];
    }

    /**
     * @return array<string, array<mixed>>
     */
    protected function contactRules(): array
    {
        return [
            'contacts' => ['array', 'max:'.Employee::MAX_EMERGENCY_CONTACTS],
            'contacts.*.name' => ['required', 'string', 'max:150'],
            'contacts.*.relationship' => ['required', Rule::in(array_filter([
                ...array_keys(LookupItem::options(LookupCategory::Relationship)),
                ...($this->personalRecord()?->emergencyContacts()->pluck('relationship')->all() ?? []),
            ]))],
            'contacts.*.phone' => ['required', 'string', 'max:20', 'regex:/^\+?[0-9][0-9 -]{6,18}$/'],
        ];
    }

    /**
     * Emergency contact rows with anything typed in, keyed by their row on the form so errors show
     * against the right row; untouched rows are dropped.
     *
     * @return array<int, array<string, string|null>>
     */
    protected function filledContactRows(): array
    {
        $rows = is_array($this->input('contacts')) ? $this->input('contacts') : [];

        return array_filter(
            array_map(fn ($row): array => is_array($row) ? array_map(fn ($value): ?string => filled($value) ? trim((string) $value) : null, $row) : [], $rows),
            fn (array $row): bool => collect($row)->filter()->isNotEmpty(),
        );
    }

    /**
     * The emergency contacts in form order.
     *
     * @return list<array{name: string, relationship: string, phone: string}>
     */
    public function emergencyContacts(): array
    {
        $contacts = $this->validated('contacts') ?? [];
        ksort($contacts);

        return array_values(array_map(fn (array $contact): array => [
            'name' => $contact['name'],
            'relationship' => $contact['relationship'],
            'phone' => $contact['phone'],
        ], $contacts));
    }

    /**
     * Keep the spouse answer only for married staff.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function tidyPersonalDetails(array $data): array
    {
        if (array_key_exists('marital_status', $data) || array_key_exists('spouse_working', $data)) {
            $data['spouse_working'] = ($data['marital_status'] ?? null) === 'married' ? ($data['spouse_working'] ?? null) : null;
        }

        return $data;
    }

    /**
     * Active codes of a category, plus the code already stored on this record.
     */
    protected function lookup(LookupCategory $category, string $field): In
    {
        return Rule::in(array_filter([...array_keys(LookupItem::options($category)), $this->personalRecord()?->{$field}]));
    }

    /**
     * @return array<string, string>
     */
    protected function personalMessages(): array
    {
        return [
            'phone.regex' => __('Enter a phone number, e.g. 012-345 6789 or +60 12-345 6789.'),
            'contacts.*.phone.regex' => __('Enter a phone number, e.g. 012-345 6789 or +60 12-345 6789.'),
            'contacts.max' => __('Add up to :max emergency contacts.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function personalAttributes(): array
    {
        return [
            'gender' => __('Gender'),
            'race' => __('Race'),
            'marital_status' => __('Marital status'),
            'children_count' => __('Number of children'),
            'spouse_working' => __('Spouse is working'),
            'phone' => __('Phone'),
            'personal_email' => __('Personal email'),
            'address_line_1' => __('Address line 1'),
            'address_line_2' => __('Address line 2'),
            'postcode' => __('Postcode'),
            'city' => __('City'),
            'state' => __('State'),
            'contacts.*.name' => __('Contact name'),
            'contacts.*.relationship' => __('Relationship'),
            'contacts.*.phone' => __('Contact phone'),
        ];
    }
}
