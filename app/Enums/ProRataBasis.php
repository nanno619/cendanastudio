<?php

namespace App\Enums;

/**
 * Which days a monthly wage is divided by when paying part of a month, set on Settings → Payroll → Pro-Rata.
 * Labels are English and translated with __() where shown.
 */
enum ProRataBasis: string
{
    case CalendarDays = 'calendar-days';
    case WorkingDays = 'working-days';
    case FixedDays = 'fixed-days';

    public function label(): string
    {
        return match ($this) {
            self::CalendarDays => 'Calendar days in the month',
            self::WorkingDays => 'Working days in the month',
            self::FixedDays => 'A fixed number of days',
        };
    }
}
