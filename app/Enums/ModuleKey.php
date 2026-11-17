<?php

namespace App\Enums;

/**
 * The app's switchable modules. Each has a row in `modules` (name, icon, order, on/off, roles) managed on
 * Administrations → Modules; add a case (and a ModuleSeeder default) to add a module. Default names are
 * English and translated with __() where shown.
 */
enum ModuleKey: string
{
    case Leave = 'leave';
    case Payroll = 'payroll';
    case Claim = 'claim';
    case Hadir = 'hadir';

    public function defaultName(): string
    {
        return match ($this) {
            self::Leave => 'Leave',
            self::Payroll => 'Payroll',
            self::Claim => 'Claim',
            self::Hadir => 'Hadir',
        };
    }

    public function defaultIcon(): string
    {
        return match ($this) {
            self::Leave => 'calendar',
            self::Payroll => 'dollar',
            self::Claim => 'file-text',
            self::Hadir => 'clock',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Leave => 'Leave applications, balances and approval.',
            self::Payroll => 'Monthly payroll, statutory contributions and payslips.',
            self::Claim => 'Expense claims and their approval.',
            self::Hadir => 'Attendance, overtime hours and visits.',
        };
    }

    /**
     * Position in the sidebar before an order is saved.
     */
    public function defaultSortOrder(): int
    {
        return array_search($this, self::cases(), true) + 1;
    }
}
