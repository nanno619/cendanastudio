<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\LeaveApprovalRoute;
use Illuminate\Database\Seeder;

class LeaveApprovalRouteSeeder extends Seeder
{
    /**
     * The default leave approval route (all environments): Admin approves. An existing default
     * route is left untouched, so changes saved by Super Admin survive re-seeding.
     */
    public function run(): void
    {
        activity()->withoutLogging(function (): void {
            if (LeaveApprovalRoute::query()->whereNull('applicant_role')->doesntExist()) {
                LeaveApprovalRoute::create(['applicant_role' => null, 'levels' => [['type' => 'role', 'value' => Role::Admin->value]]]);
            }
        });
    }
}
