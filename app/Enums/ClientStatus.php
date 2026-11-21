<?php

namespace App\Enums;

/**
 * Where a client stands with the company (CRM). Closure and Blacklist need a reason. Labels are English
 * and translated with __() where shown; tones are <x-ui.badge> variants.
 */
enum ClientStatus: string
{
    case Active = 'active';
    case Closure = 'closure';
    case Blacklist = 'blacklist';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::Closure => 'Closure',
            self::Blacklist => 'Blacklist',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Active => 'Current clients.',
            self::Closure => 'No longer a client.',
            self::Blacklist => 'Don’t do business with them.',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Active => 'success',
            self::Closure => 'neutral',
            self::Blacklist => 'danger',
        };
    }

    public function needsReason(): bool
    {
        return $this !== self::Active;
    }
}
