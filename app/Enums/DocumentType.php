<?php

namespace App\Enums;

/**
 * Kinds of document kept on a staff record. Labels are English and translated with __() where shown.
 */
enum DocumentType: string
{
    case IcCopy = 'ic-copy';
    case OfferLetter = 'offer-letter';
    case Certificate = 'certificate';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::IcCopy => 'IC or passport copy',
            self::OfferLetter => 'Offer letter',
            self::Certificate => 'Certificate',
            self::Other => 'Other',
        };
    }
}
