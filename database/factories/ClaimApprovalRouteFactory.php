<?php

namespace Database\Factories;

use App\Enums\Role;
use App\Models\ClaimApprovalRoute;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClaimApprovalRoute>
 */
class ClaimApprovalRouteFactory extends Factory
{
    /**
     * The default route, approved by Finance.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'applicant_role' => null,
            'levels' => [['type' => 'role', 'value' => Role::Finance->value, 'above' => null]],
        ];
    }

    public function forRole(Role $role): static
    {
        return $this->state(['applicant_role' => $role]);
    }
}
