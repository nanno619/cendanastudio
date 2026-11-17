<?php

namespace App\Enums;

/**
 * Why a staff member left. Labels are English and translated with __() where shown.
 */
enum LeavingReason: string
{
    case Resigned = 'resigned';
    case Terminated = 'terminated';
    case ContractEnded = 'contract-ended';
    case Retired = 'retired';
    case Deceased = 'deceased';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Resigned => 'Resigned',
            self::Terminated => 'Terminated',
            self::ContractEnded => 'Contract ended',
            self::Retired => 'Retired',
            self::Deceased => 'Deceased',
            self::Other => 'Other',
        };
    }
}
