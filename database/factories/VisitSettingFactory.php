<?php

namespace Database\Factories;

use App\Models\VisitSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<VisitSetting>
 */
class VisitSettingFactory extends Factory
{
    /**
     * The default visit rules.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return VisitSetting::defaults();
    }
}
