<?php

namespace Database\Factories;

use App\Models\AttendanceSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AttendanceSetting>
 */
class AttendanceSettingFactory extends Factory
{
    /**
     * The default attendance rules.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return AttendanceSetting::defaults();
    }

    /**
     * A geofence around Kuala Lumpur City Centre.
     */
    public function withGeofence(): static
    {
        return $this->state([
            'geofence_enabled' => true,
            'office_latitude' => '3.157800',
            'office_longitude' => '101.711900',
            'geofence_radius_m' => 200,
        ]);
    }
}
