<?php

namespace Database\Factories;

use App\Enums\EmploymentType;
use App\Enums\IdentityType;
use App\Enums\LeavingReason;
use App\Models\Employee;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * A confirmed, permanent Malaysian staff member with a sign-in account.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $birthDate = CarbonImmutable::instance(fake()->dateTimeBetween('-55 years', '-21 years'));

        return [
            'user_id' => User::factory(),
            'staff_no' => 'S'.fake()->unique()->numerify('####'),
            'identity_type' => IdentityType::MyKad,
            'identity_no' => $birthDate->format('ymd').fake()->unique()->numerify('######'),
            'nationality' => 'MY',
            'date_of_birth' => $birthDate,
            'children_count' => 0,
            'employment_type' => EmploymentType::Permanent,
            'join_date' => CarbonImmutable::instance(fake()->dateTimeBetween('-5 years', '-6 months')),
            'probation_end_date' => null,
            'is_manual_worker' => false,
            'basic_salary' => fake()->randomElement(['3500.00', '4800.00', '6200.00']),
        ];
    }

    public function onProbation(): static
    {
        return $this->state(['join_date' => Employee::today()->subMonth(), 'probation_end_date' => Employee::today()->addMonths(2)]);
    }

    public function leaving(): static
    {
        return $this->state(['last_working_date' => Employee::today()->addWeeks(2), 'leaving_reason' => LeavingReason::Resigned]);
    }

    public function left(): static
    {
        return $this->state(['last_working_date' => Employee::today()->subDay(), 'leaving_reason' => LeavingReason::Resigned]);
    }
}
