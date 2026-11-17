<?php

namespace App\Enums;

/**
 * Who is paid overtime, set on Settings → Payroll → Overtime.
 * Labels are English and translated with __() where shown.
 */
enum OvertimeEligibility: string
{
    case EmploymentAct = 'employment-act';
    case AllStaff = 'all-staff';

    public function label(): string
    {
        return match ($this) {
            self::EmploymentAct => 'Staff covered by the Employment Act',
            self::AllStaff => 'All staff',
        };
    }
}
