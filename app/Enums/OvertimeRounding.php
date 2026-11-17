<?php

namespace App\Enums;

/**
 * How overtime minutes are rounded before pay, set on Settings → Hadir → Overtime. Always rounded down,
 * so staff are never paid for time they didn't work. Labels are English and translated with __() where shown.
 */
enum OvertimeRounding: string
{
    case Exact = 'exact';
    case DownToQuarterHour = 'down-15';
    case DownToHalfHour = 'down-30';
    case DownToHour = 'down-60';

    public function label(): string
    {
        return match ($this) {
            self::Exact => 'Exact minutes',
            self::DownToQuarterHour => 'Down to the nearest 15 minutes',
            self::DownToHalfHour => 'Down to the nearest 30 minutes',
            self::DownToHour => 'Down to the nearest hour',
        };
    }

    /**
     * The block size in minutes.
     */
    public function minutes(): int
    {
        return match ($this) {
            self::Exact => 1,
            self::DownToQuarterHour => 15,
            self::DownToHalfHour => 30,
            self::DownToHour => 60,
        };
    }
}
