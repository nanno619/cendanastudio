<?php

namespace App\Support;

use App\Enums\EmploymentType;
use App\Enums\LeavingReason;
use App\Enums\LookupCategory;
use App\Enums\Role;
use App\Models\Employee;
use App\Models\EpfSetting;
use App\Models\LookupItem;
use App\Models\User;

/**
 * Dropdown options for the staff add/edit form. Lookups offer active items plus the code already stored
 * on the record, so a since-deactivated value stays selectable.
 */
class StaffForm
{
    /**
     * Options for the personal details and emergency contact fields, also used by My profile.
     *
     * @return array{genders: array<string, string>, races: array<string, string>, maritalStatuses: array<string, string>, states: array<string, string>, relationships: array<string, string>}
     */
    public static function personalOptions(?Employee $employee): array
    {
        return [
            'genders' => self::lookup(LookupCategory::Gender, $employee?->gender),
            'races' => self::lookup(LookupCategory::Race, $employee?->race),
            'maritalStatuses' => self::lookup(LookupCategory::MaritalStatus, $employee?->marital_status),
            'states' => self::lookup(LookupCategory::MalaysianStates, $employee?->state),
            'relationships' => self::relationships($employee),
        ];
    }

    /**
     * Active items of a category, plus the stored code if it has since been deactivated.
     *
     * @return array<string, string>
     */
    private static function lookup(LookupCategory $category, ?string $current): array
    {
        $options = LookupItem::options($category);

        if (filled($current) && ! isset($options[$current])) {
            $options[$current] = LookupItem::labels($category)[$current] ?? $current;
        }

        return $options;
    }

    /**
     * Active relationships plus any still used by this record's contacts.
     *
     * @return array<string, string>
     */
    private static function relationships(?Employee $employee): array
    {
        $options = LookupItem::options(LookupCategory::Relationship);
        $labels = LookupItem::labels(LookupCategory::Relationship);

        foreach ($employee?->emergencyContacts ?? [] as $contact) {
            $options[$contact->relationship] ??= $labels[$contact->relationship] ?? $contact->relationship;
        }

        return $options;
    }

    /**
     * @return array<string, mixed>
     */
    public static function options(User $actor, ?Employee $employee = null): array
    {
        $epf = EpfSetting::current();

        return [
            'roles' => collect(Role::cases())
                ->reject(fn (Role $role): bool => $role === Role::SuperAdmin && ! $actor->hasRole(Role::SuperAdmin))
                ->mapWithKeys(fn (Role $role): array => [$role->value => __($role->value)])->all(),
            ...self::personalOptions($employee),
            'departments' => self::lookup(LookupCategory::Department, $employee?->department),
            'positions' => self::lookup(LookupCategory::Position, $employee?->position),
            'subsidiaries' => self::lookup(LookupCategory::Subsidiary, $employee?->subsidiary),
            'banks' => self::lookup(LookupCategory::Banks, $employee?->bank),
            'employmentTypes' => collect(EmploymentType::cases())->mapWithKeys(fn (EmploymentType $type): array => [$type->value => __($type->label())])->all(),
            'leavingReasons' => collect(LeavingReason::cases())->mapWithKeys(fn (LeavingReason $reason): array => [$reason->value => __($reason->label())])->all(),
            'managers' => Employee::query()
                ->withStatus(null)
                ->when($employee?->exists, fn ($query) => $query->whereKeyNot($employee->id))
                ->join('users', 'users.id', '=', 'employees.user_id')
                ->orderBy('users.name')
                ->pluck('users.name', 'employees.ulid')
                ->all(),
            'electedRateAllowed' => $epf->allow_employee_elected_rate,
            'statutoryEmployeeRate' => $epf->malaysian_employee_rate,
        ];
    }
}
