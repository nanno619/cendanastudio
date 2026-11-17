<?php

namespace Database\Factories;

use App\Models\ClaimSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClaimSetting>
 */
class ClaimSettingFactory extends Factory
{
    /**
     * The default claim rules.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return ClaimSetting::defaults();
    }
}
