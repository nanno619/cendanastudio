<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\LeaveApprovalRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveApprovalRoute>
 */
class LeaveApprovalRouteFactory extends Factory
{
    /**
     * The default route, approved by Admin.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_role' => null,
            'levels' => [['type' => 'role', 'value' => Role::Admin->value]],
        ];
    }

    public function forRole(Role $role): static
    {
        return $this->state(['applicant_role' => $role]);
    }
}
