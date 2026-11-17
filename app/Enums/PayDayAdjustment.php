<?php

namespace App\Enums;

/**
 * Which working day salaries move to when pay day is a rest day or public holiday.
 * Labels are English and translated with __() where shown.
 */
enum PayDayAdjustment: string
{
    case PreviousWorkingDay = 'previous-working-day';
    case NextWorkingDay = 'next-working-day';

    public function label(): string
    {
        return match ($this) {
            self::PreviousWorkingDay => 'The working day before',
            self::NextWorkingDay => 'The working day after',
        };
    }
}
