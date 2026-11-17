<?php

namespace Database\Factories;

use App\Models\AttendanceOvertimeSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceOvertimeSetting>
 */
class AttendanceOvertimeSettingFactory extends Factory
{
    /**
     * The default overtime capture rules.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return AttendanceOvertimeSetting::defaults();
    }
}
