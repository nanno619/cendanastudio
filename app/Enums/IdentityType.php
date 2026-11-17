<?php

namespace App\Enums;

/**
 * The identity document a staff record is keyed by. Labels are English and translated with __() where shown.
 */
enum IdentityType: string
{
    case MyKad = 'mykad';
    case Passport = 'passport';

    public function label(): string
    {
        return match ($this) {
            self::MyKad => 'MyKad',
            self::Passport => 'Passport',
        };
    }
}
