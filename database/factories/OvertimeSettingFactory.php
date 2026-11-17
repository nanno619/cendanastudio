<?php

namespace Database\Factories;

use App\Models\OvertimeSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<OvertimeSetting>
 */
class OvertimeSettingFactory extends Factory
{
    /**
     * The Employment Act overtime rules.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return OvertimeSetting::defaults();
    }
}
