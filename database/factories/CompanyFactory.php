<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Company>
 */
class CompanyFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->company().' Sdn. Bhd.',
            'registration_no' => fake()->numerify('20####0#####'),
            'old_registration_no' => fake()->numerify('#######').'-'.strtoupper(fake()->randomLetter()),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('03-#### ####'),
            'website' => fake()->url(),
            'address_line_1' => fake()->buildingNumber().', Jalan '.fake()->lastName(),
            'address_line_2' => null,
            'postcode' => fake()->numerify('#####'),
            'city' => fake()->randomElement(['Shah Alam', 'Petaling Jaya', 'Kuala Lumpur', 'Johor Bahru', 'George Town']),
            'state' => fake()->randomElement(['selangor', 'johor', 'penang', 'kuala-lumpur']),
            'country' => 'MY',
            'epf_no' => fake()->numerify('########'),
            'socso_no' => fake()->numerify('E##########'),
            'eis_no' => fake()->numerify('E##########'),
            'income_tax_no' => fake()->numerify('E##########'),
            'zakat_no' => null,
        ];
    }
}
