<?php

namespace App\Models;

use App\Enums\MissedClockOut;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\AttendanceSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Company-wide attendance rules (Settings → Hadir → General). One row only; use current(), which holds
 * the defaults until the settings are first saved. Working days and public holidays come from Leave →
 * General; overtime capture is on Hadir → Overtime and work away from the office on Hadir → Visits.
 *
 * @property int $id
 * @property string $ulid
 * @property string $work_start_time HH:MM, in cendana.timezone
 * @property string $work_end_time HH:MM, after the start time
 * @property int $break_minutes Unpaid break inside the work day
 * @property int $late_grace_minutes Clocking in this many minutes after the start time still counts as on time
 * @property MissedClockOut $missed_clock_out
 * @property bool $record_location Clock-ins and clock-outs save the device's location
 * @property bool $geofence_enabled Staff can only clock in near the office
 * @property string|null $office_latitude Set only while the geofence is on
 * @property string|null $office_longitude
 * @property int|null $geofence_radius_m
 * @property bool $require_selfie
 */
class AttendanceSetting extends Model
{
    /** @use HasFactory<AttendanceSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Columns that only matter while the geofence is on.
     *
     * @var list<string>
     */
    public const array GEOFENCE_FIELDS = ['office_latitude', 'office_longitude', 'geofence_radius_m'];

    private const float EARTH_RADIUS_M = 6371000.0;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'work_start_time', 'work_end_time', 'break_minutes', 'late_grace_minutes', 'missed_clock_out',
        'record_location', 'geofence_enabled', ...self::GEOFENCE_FIELDS, 'require_selfie',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'break_minutes' => 'integer',
            'late_grace_minutes' => 'integer',
            'missed_clock_out' => MissedClockOut::class,
            'record_location' => 'boolean',
            'geofence_enabled' => 'boolean',
            'office_latitude' => 'decimal:6',
            'office_longitude' => 'decimal:6',
            'geofence_radius_m' => 'integer',
            'require_selfie' => 'boolean',
        ];
    }

    /**
     * The saved settings, or an unsaved row holding the defaults.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static(static::defaults());
    }

    /**
     * Office hours of 9 to 6 with an hour's break: 8 working hours, within the Employment Act's daily limit.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'work_start_time' => '09:00',
            'work_end_time' => '18:00',
            'break_minutes' => 60,
            'late_grace_minutes' => 10,
            'missed_clock_out' => MissedClockOut::FlagForReview,
            'record_location' => true,
            'geofence_enabled' => false,
            'office_latitude' => null,
            'office_longitude' => null,
            'geofence_radius_m' => null,
            'require_selfie' => false,
        ];
    }

    /**
     * Working minutes in a normal day: end minus start minus the break.
     */
    public function workingMinutesPerDay(): int
    {
        return $this->minutesOf($this->work_end_time) - $this->minutesOf($this->work_start_time) - $this->break_minutes;
    }

    /**
     * Whether a clock-in at this local time is late, after the grace period.
     */
    public function isLate(CarbonInterface $clockIn): bool
    {
        $localTime = CarbonImmutable::parse($clockIn)->timezone(config('cendana.timezone'));

        return $localTime->hour * 60 + $localTime->minute > $this->minutesOf($this->work_start_time) + $this->late_grace_minutes;
    }

    /**
     * Whether a location is inside the geofence. Always true while the geofence is off.
     */
    public function isWithinGeofence(float $latitude, float $longitude): bool
    {
        if (! $this->geofence_enabled) {
            return true;
        }

        $fromLatitude = deg2rad((float) $this->office_latitude);
        $toLatitude = deg2rad($latitude);
        $deltaLatitude = $toLatitude - $fromLatitude;
        $deltaLongitude = deg2rad($longitude - (float) $this->office_longitude);

        $a = sin($deltaLatitude / 2) ** 2 + cos($fromLatitude) * cos($toLatitude) * sin($deltaLongitude / 2) ** 2;

        return self::EARTH_RADIUS_M * 2 * atan2(sqrt($a), sqrt(1 - $a)) <= $this->geofence_radius_m;
    }

    private function minutesOf(string $time): int
    {
        [$hours, $minutes] = array_map('intval', explode(':', $time));

        return $hours * 60 + $minutes;
    }

    /**
     * The ULID is the public identifier; `id` stays the incrementing primary key.
     *
     * @return list<string>
     */
    public function uniqueIds(): array
    {
        return ['ulid'];
    }

    public function getRouteKeyName(): string
    {
        return 'ulid';
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (): string => 'Updated attendance settings');
    }
}
