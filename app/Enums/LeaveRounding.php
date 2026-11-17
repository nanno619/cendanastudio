<?php

namespace App\Enums;

/**
 * How a prorated leave entitlement is rounded on Settings → Leave → General.
 * Labels are English and translated with __() where shown.
 */
enum LeaveRounding: string
{
    case NearestDay = 'nearest-day';
    case NearestHalf = 'nearest-half';
    case Down = 'down';
    case Up = 'up';

    public function label(): string
    {
        return match ($this) {
            self::NearestDay => 'Nearest day (Employment Act)',
            self::NearestHalf => 'Nearest half day',
            self::Down => 'Always round down',
            self::Up => 'Always round up',
        };
    }
}
