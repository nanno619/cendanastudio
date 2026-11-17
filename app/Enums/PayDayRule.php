<?php

namespace App\Enums;

/**
 * When salaries are paid each month, set on Settings → Payroll → General.
 * Labels are English and translated with __() where shown.
 */
enum PayDayRule: string
{
    case FixedDay = 'fixed-day';
    case LastWorkingDay = 'last-working-day';
    case NextMonthDay = 'next-month-day';

    public function label(): string
    {
        return match ($this) {
            self::FixedDay => 'On a fixed day of the month',
            self::LastWorkingDay => 'On the last working day of the month',
            self::NextMonthDay => 'On a fixed day of the following month',
        };
    }
}
