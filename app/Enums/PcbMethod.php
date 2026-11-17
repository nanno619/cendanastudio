<?php

namespace App\Enums;

/**
 * How PCB (monthly tax deduction) is worked out, following LHDN.
 * Labels are English and translated with __() where shown.
 */
enum PcbMethod: string
{
    case Computerised = 'computerised';
    case Schedule = 'schedule';

    public function label(): string
    {
        return match ($this) {
            self::Computerised => 'Computerised calculation (LHDN)',
            self::Schedule => 'PCB schedule table',
        };
    }
}
