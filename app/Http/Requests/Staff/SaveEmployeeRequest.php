<?php

namespace App\Http\Requests\Staff;

use App\Enums\EmploymentType;
use App\Enums\IdentityType;
use App\Enums\LeavingReason;
use App\Enums\LookupCategory;
use App\Enums\Role;
use App\Http\Requests\Staff\Concerns\ValidatesPersonalDetails;
use App\Models\Employee;
use App\Models\EpfSetting;
use App\Support\Countries;
use App\Support\MyKad;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Validates adding (POST) or editing (PUT) a staff record and its sign-in account. Only Super Admin can
 * give the Super Admin role or edit a Super Admin's record. Lookups accept active codes, plus the stored
 * code when editing so a since-deactivated value can be kept. The date of birth comes from the MyKad.
 */
class SaveEmployeeRequest extends FormRequest
{
    use ValidatesPersonalDetails;

    public function authorize(): bool
    {
        $actor = $this->user();

        if ($actor === null || ! $actor->hasAnyRole(Employee::RECORD_MANAGER_ROLES)) {
            return false;
        }

        return $this->employee()?->isEditableBy($actor) ?? true;
    }

    public function employee(): ?Employee
    {
        $employee = $this->route('employee');

        return $employee instanceof Employee ? $employee : null;
    }

    protected function personalRecord(): ?Employee
    {
        return $this->employee();
    }

    /**
     * Tidy identifiers before validating: MyKad and account numbers lose spaces and dashes, codes and
     * passport numbers are uppercased, blank optional numbers become null.
     */
    protected function prepareForValidation(): void
    {
        $upper = fn (string $key): ?string => filled($this->input($key)) ? strtoupper(preg_replace('/\s+/', '', (string) $this->input($key))) : null;
        $identity = (string) $this->input('identity_no');

        $this->merge([
            'staff_no' => $upper('staff_no'),
            'identity_no' => $this->input('identity_type') === IdentityType::MyKad->value ? MyKad::normalize($identity) : $upper('identity_no'),
            'bank_account_no' => filled($this->input('bank_account_no')) ? preg_replace('/[\s-]/', '', (string) $this->input('bank_account_no')) : null,
            'epf_no' => $upper('epf_no'),
            'socso_no' => $upper('socso_no'),
            'income_tax_no' => $upper('income_tax_no'),
            'email' => strtolower(trim((string) $this->input('email'))),
            'contacts' => $this->filledContactRows(),
        ]);
    }

    /**
     * @return array<string, ValidationRule|Closure|array<mixed>|string>
     */
    public function rules(): array
    {
        $employee = $this->employee();
        $epf = EpfSetting::current();
        $roles = $this->user()?->hasRole(Role::SuperAdmin) ? Rule::enum(Role::class) : Rule::enum(Role::class)->except([Role::SuperAdmin]);

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($employee?->user_id)],
            'role' => ['required', $roles],

            'staff_no' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9-]+$/', Rule::unique('employees', 'staff_no')->ignore($employee?->id)],
            'identity_type' => ['required', Rule::enum(IdentityType::class)],
            'identity_no' => ['required', 'string', 'max:20', Rule::unique('employees', 'identity_no')->ignore($employee?->id), function (string $attribute, mixed $value, Closure $fail): void {
                $valid = $this->input('identity_type') === IdentityType::MyKad->value
                    ? MyKad::birthDate((string) $value) !== null
                    : (bool) preg_match('/^[A-Z0-9]{6,20}$/', (string) $value);

                if (! $valid) {
                    $fail($this->input('identity_type') === IdentityType::MyKad->value
                        ? __('Enter a 12-digit MyKad number starting with a real date of birth, e.g. 900101-14-5678.')
                        : __('Enter 6 to 20 letters and numbers.'));
                }
            }],
            'nationality' => ['required', Rule::in(Countries::codes()), Rule::when($this->input('identity_type') === IdentityType::MyKad->value, Rule::in([Countries::MALAYSIA]))],
            'date_of_birth' => ['exclude_if:identity_type,'.IdentityType::MyKad->value, 'required', 'date', 'before:today'],
            // Children is sent only from the edit form; new records start at 0 until the staff member fills it in.
            ...$this->personalRules(childrenRequired: false),

            'employment_type' => ['required', Rule::enum(EmploymentType::class)],
            'department' => ['nullable', $this->lookup(LookupCategory::Department, 'department')],
            'position' => ['nullable', $this->lookup(LookupCategory::Position, 'position')],
            'reporting_manager' => ['nullable', 'string', Rule::exists('employees', 'ulid'), Rule::notIn(array_filter([$employee?->ulid]))],
            'join_date' => ['required', 'date'],
            'probation_end_date' => ['nullable', 'date', 'after:join_date'],
            'is_manual_worker' => ['required', 'boolean'],
            'last_working_date' => ['nullable', 'date', 'after_or_equal:join_date', Rule::prohibitedIf($employee !== null && $employee->user_id === $this->user()?->id)],
            'leaving_reason' => ['nullable', 'required_with:last_working_date', Rule::enum(LeavingReason::class)],

            'basic_salary' => ['required', 'numeric', 'decimal:0,2', 'between:0,1000000'],
            'bank' => ['nullable', $this->lookup(LookupCategory::Banks, 'bank')],
            'bank_account_no' => ['nullable', 'regex:/^\d{5,20}$/'],

            'epf_no' => ['nullable', 'string', 'max:20'],
            'socso_no' => ['nullable', 'string', 'max:20'],
            'income_tax_no' => ['nullable', 'string', 'max:20'],
            'epf_elected_rate' => [
                'nullable', Rule::prohibitedIf(! $epf->allow_employee_elected_rate), 'numeric', 'decimal:0,2', 'max:30',
                'gt:'.$epf->malaysian_employee_rate,
            ],

            ...$this->contactRules(),
        ];
    }

    /**
     * The sign-in account's fields.
     *
     * @return array{name: string, email: string}
     */
    public function userAttributes(): array
    {
        return ['name' => $this->validated('name'), 'email' => $this->validated('email')];
    }

    public function role(): Role
    {
        return Role::from($this->validated('role'));
    }

    /**
     * The staff record's fields, with the date of birth from the MyKad, the manager resolved from its ULID,
     * the spouse answer kept only for married staff and the leaving reason only with a last working day.
     *
     * @return array<string, mixed>
     */
    public function employeeAttributes(): array
    {
        $data = collect($this->validated())->except(['name', 'email', 'role', 'reporting_manager', 'contacts'])->all();

        if ($data['identity_type'] === IdentityType::MyKad->value) {
            $data['date_of_birth'] = MyKad::birthDate($data['identity_no']);
        }

        $data['reporting_manager_id'] = filled($this->validated('reporting_manager'))
            ? Employee::query()->where('ulid', $this->validated('reporting_manager'))->value('id')
            : null;
        $data = $this->tidyPersonalDetails($data);
        if (array_key_exists('last_working_date', $data)) {
            $data['leaving_reason'] = filled($data['last_working_date']) ? ($data['leaving_reason'] ?? null) : null;
        }
        $data['epf_elected_rate'] ??= null;

        return $data;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            ...$this->personalMessages(),
            'staff_no.regex' => __('Use capital letters, numbers and dashes only.'),
            'nationality.in' => __('MyKad holders are Malaysian. Choose Passport for other nationalities.'),
            'bank_account_no.regex' => __('Enter 5 to 20 digits, e.g. 514123456789.'),
            'reporting_manager.not_in' => __('A staff member can’t report to themselves.'),
            'last_working_date.prohibited' => __('You can’t set a last working day on your own record.'),
            'epf_elected_rate.prohibited' => __('EPF settings don’t allow employees to elect a higher rate.'),
            'epf_elected_rate.gt' => __('The elected rate must be higher than the statutory employee rate.'),
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            ...$this->personalAttributes(),
            'name' => __('Full name'),
            'email' => __('Work email'),
            'role' => __('Role'),
            'staff_no' => __('Staff number'),
            'identity_type' => __('Identity document'),
            'identity_no' => __('IC or passport number'),
            'nationality' => __('Nationality'),
            'date_of_birth' => __('Date of birth'),
            'employment_type' => __('Employment type'),
            'department' => __('Department'),
            'position' => __('Position'),
            'reporting_manager' => __('Reports to'),
            'join_date' => __('Join date'),
            'probation_end_date' => __('Probation ends'),
            'is_manual_worker' => __('Manual worker'),
            'last_working_date' => __('Last working day'),
            'leaving_reason' => __('Reason for leaving'),
            'basic_salary' => __('Basic salary'),
            'bank' => __('Bank'),
            'bank_account_no' => __('Account number'),
            'epf_no' => __('EPF number'),
            'socso_no' => __('SOCSO number'),
            'income_tax_no' => __('Income tax number'),
            'epf_elected_rate' => __('Elected EPF rate'),
        ];
    }
}
