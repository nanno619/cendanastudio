<?php

namespace App\Models;

use App\Enums\EpfCalculationMethod;
use Database\Factories\EpfSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * EPF (KWSP) contribution rules (Settings → Payroll → EPF). One row only; use current(), which holds
 * the statutory defaults until the settings are first saved. Rates are percentages of monthly wages;
 * the 60-and-over rates apply from the month after the 60th birthday. Employer EPF numbers live on Company.
 *
 * @property int $id
 * @property string $ulid
 * @property string $malaysian_employee_rate Malaysian staff under 60
 * @property string $malaysian_employer_rate For wages up to wage_threshold
 * @property string $malaysian_employer_rate_above_threshold For wages above wage_threshold
 * @property string $wage_threshold Monthly wages in RM
 * @property string $senior_employee_rate Malaysian staff 60 and over
 * @property string $senior_employer_rate
 * @property string $foreign_employee_rate Non-Malaysian staff
 * @property string $foreign_employer_rate
 * @property EpfCalculationMethod $calculation_method
 * @property bool $allow_employer_top_up
 * @property string|null $employer_top_up_rate Employer rate for Malaysian staff under 60, set only while top-up is on
 * @property bool $allow_employee_elected_rate Staff may choose a rate above the statutory employee rate
 */
class EpfSetting extends Model
{
    /** @use HasFactory<EpfSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Contributions are due to KWSP by this day of the following month.
     */
    public const int PAYMENT_DUE_DAY = 15;

    /**
     * Columns holding a contribution rate, in the order they appear on the page.
     *
     * @var list<string>
     */
    public const array RATE_FIELDS = [
        'malaysian_employee_rate', 'malaysian_employer_rate', 'malaysian_employer_rate_above_threshold',
        'senior_employee_rate', 'senior_employer_rate', 'foreign_employee_rate', 'foreign_employer_rate',
    ];

    /**
     * @var list<string>
     */
    protected $fillable = [
        ...self::RATE_FIELDS,
        'wage_threshold', 'calculation_method',
        'allow_employer_top_up', 'employer_top_up_rate', 'allow_employee_elected_rate',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            ...array_fill_keys(self::RATE_FIELDS, 'decimal:2'),
            'wage_threshold' => 'decimal:2',
            'calculation_method' => EpfCalculationMethod::class,
            'allow_employer_top_up' => 'boolean',
            'employer_top_up_rate' => 'decimal:2',
            'allow_employee_elected_rate' => 'boolean',
        ];
    }

    /**
     * The saved settings, or an unsaved row holding the statutory defaults.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static(static::defaults());
    }

    /**
     * Statutory rates as of October 2025 (non-Malaysian contributions became mandatory then).
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'malaysian_employee_rate' => '11.00',
            'malaysian_employer_rate' => '13.00',
            'malaysian_employer_rate_above_threshold' => '12.00',
            'wage_threshold' => '5000.00',
            'senior_employee_rate' => '0.00',
            'senior_employer_rate' => '4.00',
            'foreign_employee_rate' => '2.00',
            'foreign_employer_rate' => '2.00',
            'calculation_method' => EpfCalculationMethod::ThirdSchedule,
            'allow_employer_top_up' => false,
            'employer_top_up_rate' => null,
            'allow_employee_elected_rate' => false,
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
            ->setDescriptionForEvent(fn (): string => 'Updated EPF settings');
    }
}
