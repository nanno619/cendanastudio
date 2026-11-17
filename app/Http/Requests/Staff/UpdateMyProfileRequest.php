<?php

namespace App\Http\Requests\Staff;

use App\Http\Requests\Staff\Concerns\ValidatesPersonalDetails;
use App\Models\Employee;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

/**
 * Validates a staff member editing their own record on My profile. Only personal details and emergency
 * contacts are accepted; anything else in the request (salary, IC, bank, employment) is ignored.
 */
class UpdateMyProfileRequest extends FormRequest
{
    use ValidatesPersonalDetails;

    /**
     * The personal detail columns staff may change themselves.
     *
     * @var list<string>
     */
    public const array SELF_SERVICE_FIELDS = [
        'gender', 'race', 'marital_status', 'children_count', 'spouse_working',
        'phone', 'personal_email', 'address_line_1', 'address_line_2', 'postcode', 'city', 'state',
    ];

    public function authorize(): bool
    {
        return $this->personalRecord() !== null;
    }

    protected function personalRecord(): ?Employee
    {
        return $this->user()?->employee;
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['contacts' => $this->filledContactRows()]);
    }

    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [...$this->personalRules(), ...$this->contactRules()];
    }

    /**
     * The validated personal details, with the spouse answer kept only for married staff.
     *
     * @return array<string, mixed>
     */
    public function personalDetails(): array
    {
        return $this->tidyPersonalDetails(collect($this->validated())->only(self::SELF_SERVICE_FIELDS)->all());
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return $this->personalMessages();
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return $this->personalAttributes();
    }
}
