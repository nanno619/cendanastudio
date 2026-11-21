<?php

namespace Database\Factories;

use App\Models\Client;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Client>
 */
class ClientFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company().' Sdn Bhd',
            'registration_no' => fake()->unique()->numerify('2019010#####'),
            'phone' => '03-'.fake()->numerify('#### ####'),
            'email' => fake()->unique()->companyEmail(),
            'city' => 'Kuala Lumpur',
            'state' => 'kuala-lumpur',
        ];
    }
}
