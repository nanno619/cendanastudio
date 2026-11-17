<?php

namespace App\Enums;

/**
 * How EPF (KWSP) contributions are worked out, set on Settings → Payroll → EPF.
 * Labels are English and translated with __() where shown.
 */
enum EpfCalculationMethod: string
{
    case ThirdSchedule = 'third-schedule';
    case Exact = 'exact';

    public function label(): string
    {
        return match ($this) {
            self::ThirdSchedule => 'KWSP Third Schedule table',
            self::Exact => 'Exact percentage of wages',
        };
    }
}
