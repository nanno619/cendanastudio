<?php

namespace App\Models;

use App\Enums\ProRataBasis;
use App\Enums\Weekday;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Database\Factories\ProRataSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Part-month wage rules (Settings → Payroll → Pro-Rata). One row only; use current(), which holds the
 * defaults until the settings are first saved. A part-month wage is the monthly wage ÷ the basis's days
 * × the days paid. Leave entitlement proration lives on Leave → General.
 *
 * @property int $id
 * @property string $ulid
 * @property ProRataBasis $joiner_leaver_basis Staff who join or leave during the month
 * @property ProRataBasis $unpaid_leave_basis Deductions for unpaid leave and unauthorised absence
 * @property int|null $fixed_days Set only while either basis is a fixed number of days
 * @property bool $prorate_fixed_allowances Fixed allowances are prorated like the basic wage
 */
class ProRataSetting extends Model
{
    /** @use HasFactory<ProRataSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * The Employment Act divides monthly wages by 26 for the ordinary rate of pay per day (s.60I(1A)).
     */
    public const int STATUTORY_FIXED_DAYS = 26;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'joiner_leaver_basis', 'unpaid_leave_basis', 'fixed_days', 'prorate_fixed_allowances',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'joiner_leaver_basis' => ProRataBasis::class,
            'unpaid_leave_basis' => ProRataBasis::class,
            'fixed_days' => 'integer',
            'prorate_fixed_allowances' => 'boolean',
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
     * Calendar days for joiners and leavers (Employment Act s.60I(1C)) and the ordinary rate of pay
     * (wages ÷ 26) for unpaid leave.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'joiner_leaver_basis' => ProRataBasis::CalendarDays,
            'unpaid_leave_basis' => ProRataBasis::FixedDays,
            'fixed_days' => self::STATUTORY_FIXED_DAYS,
            'prorate_fixed_allowances' => true,
        ];
    }

    /**
     * Whether either basis needs the fixed number of days.
     */
    public function usesFixedDays(): bool
    {
        return $this->joiner_leaver_basis === ProRataBasis::FixedDays
            || $this->unpaid_leave_basis === ProRataBasis::FixedDays;
    }

    /**
     * The days a monthly wage is divided by for the given month. Working days follow Leave → General,
     * with its half working day counting as half; public holidays aren't taken out.
     */
    public function daysFor(ProRataBasis $basis, CarbonInterface $month, LeaveSetting $leave): float
    {
        return match ($basis) {
            ProRataBasis::CalendarDays => (float) $month->daysInMonth,
            ProRataBasis::FixedDays => (float) ($this->fixed_days ?? self::STATUTORY_FIXED_DAYS),
            ProRataBasis::WorkingDays => $this->workingDaysIn($month, $leave),
        };
    }

    private function workingDaysIn(CarbonInterface $month, LeaveSetting $leave): float
    {
        $workingDays = $leave->working_days->map(fn (Weekday $day): int => $day->value)->all();
        $halfDay = $leave->half_working_day?->value;
        $day = CarbonImmutable::parse($month)->startOfMonth();
        $days = 0.0;

        for ($i = 0; $i < $day->daysInMonth; $i++) {
            $weekday = $day->addDays($i)->isoWeekday();

            if (in_array($weekday, $workingDays, true)) {
                $days += $weekday === $halfDay ? 0.5 : 1.0;
            }
        }

        return $days;
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
            ->setDescriptionForEvent(fn (): string => 'Updated pro-rata settings');
    }
}
