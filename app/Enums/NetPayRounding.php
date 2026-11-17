<?php

namespace App\Enums;

/**
 * How net pay is rounded on payslips.
 * Labels are English and translated with __() where shown.
 */
enum NetPayRounding: string
{
    case Sen = 'sen';
    case FiveSen = 'five-sen';
    case Ringgit = 'ringgit';

    public function label(): string
    {
        return match ($this) {
            self::Sen => 'To the sen (no rounding)',
            self::FiveSen => 'To the nearest 5 sen',
            self::Ringgit => 'To the nearest ringgit',
        };
    }
}
