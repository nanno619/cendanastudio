<?php

namespace Database\Factories;

use App\Models\LeavePolicy;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeavePolicy>
 */
class LeavePolicyFactory extends Factory
{
    /**
     * An annual leave policy with the Employment Act minimums.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'leave_type' => 'annual',
            'is_paid' => true,
            'requires_attachment' => false,
            'entitlements' => [['min_years' => 0, 'days' => 8], ['min_years' => 2, 'days' => 12], ['min_years' => 5, 'days' => 16]],
            'allow_carry_forward' => false,
            'carry_forward_max_days' => null,
            'carry_forward_expiry_months' => null,
        ];
    }
}
