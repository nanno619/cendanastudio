<?php

namespace App\Enums;

/**
 * Data Dictionary categories. Defined in code because code relies on them; Super Admin manages their items.
 * Labels and descriptions are English and translated with __() where shown.
 */
enum LookupCategory: string
{
    case MalaysianStates = 'malaysian-states';
    case Race = 'race';
    case MaritalStatus = 'marital-status';
    case LeaveType = 'leave-type';
    case ClaimType = 'claim-type';
    case Gender = 'gender';
    case Statuses = 'statuses';

    public function label(): string
    {
        return match ($this) {
            self::MalaysianStates => 'Malaysian States',
            self::Race => 'Race',
            self::MaritalStatus => 'Marital Status',
            self::LeaveType => 'Leave Type',
            self::ClaimType => 'Claim Type',
            self::Gender => 'Gender',
            self::Statuses => 'Statuses',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::MalaysianStates => 'States and federal territories used in addresses.',
            self::Race => 'Ethnic groups recorded in staff and client profiles.',
            self::MaritalStatus => 'Marital status recorded in staff profiles.',
            self::LeaveType => 'Kinds of leave staff can apply for.',
            self::ClaimType => 'Kinds of expenses staff can claim.',
            self::Gender => 'Gender recorded in staff and client profiles.',
            self::Statuses => 'Approval statuses for leave and claim applications.',
        };
    }
}
