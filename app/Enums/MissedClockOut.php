<?php

namespace App\Enums;

/**
 * What happens when staff forget to clock out, set on Settings → Hadir → General.
 * Labels are English and translated with __() where shown.
 */
enum MissedClockOut: string
{
    case FlagForReview = 'flag-for-review';
    case EndOfWorkDay = 'end-of-work-day';

    public function label(): string
    {
        return match ($this) {
            self::FlagForReview => 'Flag the day for HR to review',
            self::EndOfWorkDay => 'Clock them out at the end of the work day',
        };
    }
}
