<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\ClaimApprovalRoute;
use Illuminate\Database\Seeder;

class ClaimApprovalRouteSeeder extends Seeder
{
    /**
     * The default claim approval route (all environments): Finance approves. An existing default
     * route is left untouched, so changes saved by Super Admin survive re-seeding.
     */
    public function run(): void
    {
        activity()->withoutLogging(function (): void {
            if (ClaimApprovalRoute::query()->whereNull('applicant_role')->doesntExist()) {
                ClaimApprovalRoute::create(['applicant_role' => null, 'levels' => [['type' => 'role', 'value' => Role::Finance->value, 'above' => null]]]);
            }
        });
    }
}
