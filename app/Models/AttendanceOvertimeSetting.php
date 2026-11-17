<?php

namespace App\Models;

use App\Enums\OvertimeCapture;
use App\Enums\OvertimeRounding;
use App\Enums\Role;
use Database\Factories\AttendanceOvertimeSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * How overtime time is recorded and approved (Settings → Hadir → Overtime). One row only; use current(),
 * which holds the defaults until the settings are first saved. Who is paid and at what rate, and the
 * monthly limit, are on Payroll → Overtime (OvertimeSetting).
 *
 * @property int $id
 * @property string $ulid
 * @property OvertimeCapture $capture_method
 * @property int $minimum_minutes Shorter overtime in a day isn't counted
 * @property OvertimeRounding $rounding
 * @property bool $allow_on_rest_days
 * @property Role $approver_role Anyone with this role approves overtime
 */
class AttendanceOvertimeSetting extends Model
{
    /** @use HasFactory<AttendanceOvertimeSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * @var list<string>
     */
    protected $fillable = ['capture_method', 'minimum_minutes', 'rounding', 'allow_on_rest_days', 'approver_role'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'capture_method' => OvertimeCapture::class,
            'minimum_minutes' => 'integer',
            'rounding' => OvertimeRounding::class,
            'allow_on_rest_days' => 'boolean',
            'approver_role' => Role::class,
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
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'capture_method' => OvertimeCapture::PreApproval,
            'minimum_minutes' => 30,
            'rounding' => OvertimeRounding::DownToQuarterHour,
            'allow_on_rest_days' => true,
            'approver_role' => Role::Admin,
        ];
    }

    /**
     * The overtime minutes that count for a day: none below the minimum, then rounded down.
     */
    public function countableMinutes(int $minutes): int
    {
        if ($minutes <= 0 || $minutes < $this->minimum_minutes) {
            return 0;
        }

        $block = $this->rounding->minutes();

        return intdiv($minutes, $block) * $block;
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
            ->setDescriptionForEvent(fn (): string => 'Updated attendance overtime settings');
    }
}
