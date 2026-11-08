<?php

namespace App\Enums;

/**
 * Data Dictionary categories. Defined in code because code relies on them; Super Admin manages their items.
 * Labels and descriptions are English and translated with __() where shown.
 */
enum LookupCategory: string
{
    case MalaysianStates = 'malaysian-states';

    public function label(): string
    {
        return match ($this) {
            self::MalaysianStates => 'Malaysian States',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::MalaysianStates => 'States and federal territories used in addresses.',
        };
    }
}
