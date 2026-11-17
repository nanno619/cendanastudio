<?php

namespace App\Enums;

/**
 * What happens to unused leave when someone resigns, set on Settings → Leave → General.
 * Labels are English and translated with __() where shown.
 */
enum UnusedLeaveTreatment: string
{
    case PayOut = 'pay-out';
    case Forfeit = 'forfeit';
    case OffsetNotice = 'offset-notice';

    public function label(): string
    {
        return match ($this) {
            self::PayOut => 'Pay out in the final salary',
            self::Forfeit => 'Forfeit',
            self::OffsetNotice => 'Offset against the notice period',
        };
    }
}
