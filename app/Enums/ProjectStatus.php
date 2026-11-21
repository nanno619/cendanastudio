<?php

namespace App\Enums;

/**
 * Where a project is in its life. Labels are English and translated with __() where shown; tones are
 * <x-ui.badge> variants.
 */
enum ProjectStatus: string
{
    case Planning = 'planning';
    case Active = 'active';
    case OnHold = 'on-hold';
    case Completed = 'completed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Planning => 'Planning',
            self::Active => 'Active',
            self::OnHold => 'On hold',
            self::Completed => 'Completed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function tone(): string
    {
        return match ($this) {
            self::Planning => 'info',
            self::Active => 'success',
            self::OnHold => 'warning',
            self::Completed => 'neutral',
            self::Cancelled => 'danger',
        };
    }

    /**
     * Still being worked on (the list shows these unless filtered).
     */
    public function isOpen(): bool
    {
        return in_array($this, [self::Planning, self::Active, self::OnHold], true);
    }
}
