<?php

namespace Database\Factories;

use App\Models\LeaveSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LeaveSetting>
 */
class LeaveSettingFactory extends Factory
{
    /**
     * The default leave rules.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return LeaveSetting::defaults();
    }
}
