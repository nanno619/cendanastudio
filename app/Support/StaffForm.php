<?php

namespace App\Support;

use App\Enums\EmploymentType;
use App\Enums\IdentityType;
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
     * @return array<string, mixed>
     */
    public static function options(User $actor, ?Employee $employee = null): array
    {
        $lookup = function (LookupCategory $category, string $field) use ($employee): array {
            $options = LookupItem::options($category);
            $current = $employee?->{$field};

            if (filled($current) && ! isset($options[$current])) {
                $options[$current] = LookupItem::labels($category)[$current] ?? $current;
            }

            return $options;
        };

        $epf = EpfSetting::current();

        return [
            'roles' => collect(Role::cases())
                ->reject(fn (Role $role): bool => $role === Role::SuperAdmin && ! $actor->hasRole(Role::SuperAdmin))
                ->mapWithKeys(fn (Role $role): array => [$role->value => __($role->value)])->all(),
            'identityTypes' => collect(IdentityType::cases())->mapWithKeys(fn (IdentityType $type): array => [$type->value => __($type->label())])->all(),
            'countries' => Countries::options(),
            'genders' => $lookup(LookupCategory::Gender, 'gender'),
            'races' => $lookup(LookupCategory::Race, 'race'),
            'maritalStatuses' => $lookup(LookupCategory::MaritalStatus, 'marital_status'),
            'states' => $lookup(LookupCategory::MalaysianStates, 'state'),
            'departments' => $lookup(LookupCategory::Department, 'department'),
            'positions' => $lookup(LookupCategory::Position, 'position'),
            'banks' => $lookup(LookupCategory::Banks, 'bank'),
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
