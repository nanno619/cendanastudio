<?php

namespace App\Enums;

/**
 * How overtime is recorded, set on Settings → Hadir → Overtime.
 * Labels are English and translated with __() where shown.
 */
enum OvertimeCapture: string
{
    case PreApproval = 'pre-approval';
    case ClockRecords = 'clock-records';

    public function label(): string
    {
        return match ($this) {
            self::PreApproval => 'Staff request overtime before working it',
            self::ClockRecords => 'Worked out from clock-outs, then approved',
        };
    }
}
