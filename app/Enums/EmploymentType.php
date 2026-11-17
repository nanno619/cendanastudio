<?php

namespace App\Enums;

/**
 * Terms a staff member is employed on. Labels are English and translated with __() where shown.
 */
enum EmploymentType: string
{
    case Permanent = 'permanent';
    case Contract = 'contract';
    case PartTime = 'part-time';
    case Intern = 'intern';

    public function label(): string
    {
        return match ($this) {
            self::Permanent => 'Permanent',
            self::Contract => 'Contract',
            self::PartTime => 'Part-time',
            self::Intern => 'Intern',
        };
    }
}
