<?php

namespace Database\Factories;

use App\Enums\ModuleKey;
use App\Models\Module;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    /**
     * The Payroll module with its defaults; use forKey() for another.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return Module::defaults(ModuleKey::Payroll);
    }

    public function forKey(ModuleKey $key): static
    {
        return $this->state(Module::defaults($key));
    }

    public function disabled(): static
    {
        return $this->state(['is_enabled' => false]);
    }
}
