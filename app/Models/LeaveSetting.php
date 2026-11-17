<?php

namespace App\Models;

use App\Enums\LeaveRounding;
use App\Enums\UnusedLeaveTreatment;
use App\Enums\Weekday;
use Database\Factories\LeaveSettingFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Company-wide leave rules (Settings → Leave → General). One row only; use current(), which holds
 * the defaults until the settings are first saved. Super Admin and Admin count as HR: they always
 * see full leave details, whatever share_leave_calendar says.
 *
 * @property int $id
 * @property string $ulid
 * @property int $leave_year_start_month 1 = January
 * @property Collection<int, Weekday> $working_days
 * @property Weekday|null $half_working_day A working day that counts as half a day
 * @property string|null $public_holiday_state LookupItem code in LookupCategory::MalaysianStates
 * @property bool $allow_half_day AM/PM leave
 * @property int $annual_leave_notice_days 0 = no notice needed
 * @property int $backdate_days 0 = no backdated applications
 * @property bool $allow_leave_during_probation Annual leave only; sick and statutory leave are always allowed
 * @property bool $allow_negative_balance
 * @property string|null $max_negative_days Days staff may borrow, set only when allow_negative_balance is on
 * @property bool $prorate_entitlement For joiners and leavers, by completed months of service
 * @property LeaveRounding $proration_rounding
 * @property UnusedLeaveTreatment $resignation_unused_leave
 * @property bool $share_leave_calendar Staff see who is on leave, never the leave type
 * @property string|null $hr_notification_email Copied on every application and decision
 */
class LeaveSetting extends Model
{
    /** @use HasFactory<LeaveSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'leave_year_start_month', 'working_days', 'half_working_day', 'public_holiday_state',
        'allow_half_day', 'annual_leave_notice_days', 'backdate_days', 'allow_leave_during_probation',
        'allow_negative_balance', 'max_negative_days', 'prorate_entitlement', 'proration_rounding',
        'resignation_unused_leave', 'share_leave_calendar', 'hr_notification_email',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'leave_year_start_month' => 'integer',
            'working_days' => AsEnumCollection::of(Weekday::class),
            'half_working_day' => Weekday::class,
            'allow_half_day' => 'boolean',
            'annual_leave_notice_days' => 'integer',
            'backdate_days' => 'integer',
            'allow_leave_during_probation' => 'boolean',
            'allow_negative_balance' => 'boolean',
            'max_negative_days' => 'decimal:1',
            'prorate_entitlement' => 'boolean',
            'proration_rounding' => LeaveRounding::class,
            'resignation_unused_leave' => UnusedLeaveTreatment::class,
            'share_leave_calendar' => 'boolean',
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
            'leave_year_start_month' => 1,
            'working_days' => [Weekday::Monday, Weekday::Tuesday, Weekday::Wednesday, Weekday::Thursday, Weekday::Friday],
            'half_working_day' => null,
            'public_holiday_state' => null,
            'allow_half_day' => true,
            'annual_leave_notice_days' => 3,
            'backdate_days' => 7,
            'allow_leave_during_probation' => false,
            'allow_negative_balance' => false,
            'max_negative_days' => null,
            'prorate_entitlement' => true,
            'proration_rounding' => LeaveRounding::NearestDay,
            'resignation_unused_leave' => UnusedLeaveTreatment::OffsetNotice,
            'share_leave_calendar' => false,
            'hr_notification_email' => null,
        ];
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
            ->setDescriptionForEvent(fn (): string => 'Updated leave settings');
    }
}
