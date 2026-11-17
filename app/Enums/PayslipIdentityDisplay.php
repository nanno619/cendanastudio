<?php

namespace App\Enums;

/**
 * How the staff member's IC or passport number is printed on payslips (Settings → Payroll → Payslip).
 * Labels are English and translated with __() where shown.
 */
enum PayslipIdentityDisplay: string
{
    case Full = 'full';
    case Masked = 'masked';
    case Hidden = 'hidden';

    public function label(): string
    {
        return match ($this) {
            self::Full => 'Show in full',
            self::Masked => 'Show the last 4 digits only',
            self::Hidden => 'Don’t show',
        };
    }
}
