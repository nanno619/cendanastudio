<?php

namespace Database\Factories;

use App\Enums\ProjectStatus;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('PRJ-2026-###'),
            'name' => ucfirst(fake()->words(3, true)),
            'client_id' => Client::factory(),
            'project_manager_id' => null,
            'status' => ProjectStatus::Active,
            'start_date' => '2026-01-05',
            'end_date' => '2026-12-31',
        ];
    }
}
