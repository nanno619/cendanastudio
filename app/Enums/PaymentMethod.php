<?php

namespace App\Enums;

/**
 * How salaries are paid by default.
 * Labels are English and translated with __() where shown.
 */
enum PaymentMethod: string
{
    case BankTransfer = 'bank-transfer';
    case Cheque = 'cheque';
    case Cash = 'cash';

    public function label(): string
    {
        return match ($this) {
            self::BankTransfer => 'Bank transfer',
            self::Cheque => 'Cheque',
            self::Cash => 'Cash',
        };
    }
}
