<?php

namespace App\Enums;

/**
 * Days of the week, numbered the ISO way (Monday = 1) so they match Carbon's isoWeekday().
 * Labels are English and translated with __() where shown.
 */
enum Weekday: int
{
    case Monday = 1;
    case Tuesday = 2;
    case Wednesday = 3;
    case Thursday = 4;
    case Friday = 5;
    case Saturday = 6;
    case Sunday = 7;

    public function label(): string
    {
        return $this->name;
    }
}
