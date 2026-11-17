<?php

namespace App\Enums;

/**
 * The HRD Corp (HRDF) levy rate the company pays on wages.
 * Labels are English and translated with __() where shown.
 */
enum HrdfLevy: string
{
    case None = 'none';
    case HalfPercent = 'half-percent';
    case OnePercent = 'one-percent';

    public function label(): string
    {
        return match ($this) {
            self::None => 'Not registered',
            self::HalfPercent => '0.5% of wages',
            self::OnePercent => '1% of wages',
        };
    }
}
