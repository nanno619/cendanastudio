<?php

namespace App\Models;

use App\Enums\OvertimeEligibility;
use Database\Factories\OvertimeSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Overtime pay rules (Settings → Payroll → Overtime). One row only; use current(), which holds the
 * Employment Act 1955 defaults until the settings are first saved. Rates are multiples of the hourly
 * rate of pay (monthly wages ÷ 26 ÷ normal hours per day). How overtime time is recorded, rounded and
 * approved belongs to Hadir → Overtime.
 *
 * @property int $id
 * @property string $ulid
 * @property OvertimeEligibility $eligibility
 * @property string|null $eligibility_wage_limit Monthly wages in RM; set only for Employment Act eligibility
 * @property string $normal_day_rate Overtime on a normal working day
 * @property string $rest_day_rate Hours beyond normal hours on a rest day
 * @property string $public_holiday_rate Hours beyond normal hours on a public holiday
 * @property string $normal_hours_per_day Divides the daily rate into the hourly rate
 * @property int $monthly_limit_hours
 */
class OvertimeSetting extends Model
{
    /** @use HasFactory<OvertimeSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Employment Act minimum multipliers: s.60A(3) normal day, s.60(3) rest day, s.60D(3) public holiday.
     */
    public const float MIN_NORMAL_DAY_RATE = 1.5;

    public const float MIN_REST_DAY_RATE = 2.0;

    public const float MIN_PUBLIC_HOLIDAY_RATE = 3.0;

    /**
     * Most overtime hours allowed in a month (Employment (Limitation of Overtime Work) Regulations 1980).
     */
    public const int MAX_MONTHLY_HOURS = 104;

    /**
     * Monthly wages are divided by this to get the ordinary rate of pay per day (s.60I(1A)).
     */
    public const int ORDINARY_RATE_DIVISOR = 26;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'eligibility', 'eligibility_wage_limit',
        'normal_day_rate', 'rest_day_rate', 'public_holiday_rate',
        'normal_hours_per_day', 'monthly_limit_hours',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'eligibility' => OvertimeEligibility::class,
            'eligibility_wage_limit' => 'decimal:2',
            'normal_day_rate' => 'decimal:2',
            'rest_day_rate' => 'decimal:2',
            'public_holiday_rate' => 'decimal:2',
            'normal_hours_per_day' => 'decimal:2',
            'monthly_limit_hours' => 'integer',
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
     * Employment Act minimums and the RM4,000 wage limit in force since January 2023.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'eligibility' => OvertimeEligibility::EmploymentAct,
            'eligibility_wage_limit' => '4000.00',
            'normal_day_rate' => '1.50',
            'rest_day_rate' => '2.00',
            'public_holiday_rate' => '3.00',
            'normal_hours_per_day' => '8.00',
            'monthly_limit_hours' => self::MAX_MONTHLY_HOURS,
        ];
    }

    /**
     * The hourly rate of pay for monthly wages: wages ÷ 26 ÷ normal hours per day, unrounded.
     */
    public function hourlyRate(string|float $monthlyWage): float
    {
        return (float) $monthlyWage / self::ORDINARY_RATE_DIVISOR / (float) $this->normal_hours_per_day;
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
            ->setDescriptionForEvent(fn (): string => 'Updated overtime settings');
    }
}
