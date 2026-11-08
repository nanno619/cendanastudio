<?php

namespace Database\Factories;

use App\Enums\LookupCategory;
use App\Models\LookupItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LookupItem>
 */
class LookupItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $label = fake()->unique()->words(2, true);

        return [
            'category' => LookupCategory::MalaysianStates,
            'code' => str($label)->slug()->toString(),
            'label_en' => ucfirst($label),
            'label_ms' => null,
            'sort_order' => 0,
            'is_active' => true,
            'tone' => null,
        ];
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
