<?php

namespace App\Enums;

/**
 * How approved claims are paid out, set on Settings → Claim → General.
 * Labels are English and translated with __() where shown.
 */
enum ClaimPaymentMethod: string
{
    case WithPayroll = 'with-payroll';
    case Separate = 'separate';

    public function label(): string
    {
        return match ($this) {
            self::WithPayroll => 'With the monthly payroll',
            self::Separate => 'Separately, as approved',
        };
    }
}
