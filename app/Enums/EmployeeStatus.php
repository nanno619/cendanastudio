<?php

namespace App\Enums;

/**
 * A staff member's status, worked out from their dates (never stored). Labels are English and translated
 * with __() where shown; tones are <x-ui.badge> variants.
 */
enum EmployeeStatus: string
{
    case Active = 'active';
    case Probation = 'probation';
    case Leaving = 'leaving';
    case Left = 'left';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Probation => 'On probation',
            self::Leaving => 'Leaving',
            self::Left => 'Left',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Probation => 'info',
            self::Leaving => 'warning',
            self::Left => 'neutral',
        };
    }
}
