<?php

namespace App\Models;

use App\Enums\HrdfLevy;
use App\Enums\NetPayRounding;
use App\Enums\PayDayAdjustment;
use App\Enums\PayDayRule;
use App\Enums\PaymentMethod;
use App\Enums\PcbMethod;
use App\Enums\Role;
use Database\Factories\PayrollSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Company-wide payroll rules (Settings → Payroll → General). One row only; use current(), which holds
 * the defaults until the settings are first saved. EPF, overtime, pro-rata and payslips have their own pages.
 *
 * @property int $id
 * @property string $ulid
 * @property PayDayRule $pay_day_rule
 * @property int|null $pay_day Day of the month (1–31, or 1–7 of the following month); null for the last working day
 * @property PayDayAdjustment $pay_day_adjustment When pay day is a rest day or public holiday (see LeaveSetting)
 * @property int $cutoff_day Overtime, claims and unpaid leave after this day go into next month's payroll
 * @property bool $socso_enabled
 * @property bool $eis_enabled
 * @property bool $pcb_enabled
 * @property PcbMethod $pcb_method
 * @property HrdfLevy $hrdf_levy
 * @property bool $zakat_via_payroll Deducted monthly and offset against PCB
 * @property PaymentMethod $payment_method
 * @property NetPayRounding $net_pay_rounding
 * @property bool $requires_approval
 * @property Role|null $approver_role Set only while approval is on
 * @property bool $lock_after_release Released months are read-only; only Super Admin can reopen one
 */
class PayrollSetting extends Model
{
    /** @use HasFactory<PayrollSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Wages must be paid within this many days after the wage period ends (Employment Act 1955, s.19).
     */
    public const int LATEST_PAY_DAY_NEXT_MONTH = 7;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'pay_day_rule', 'pay_day', 'pay_day_adjustment', 'cutoff_day',
        'socso_enabled', 'eis_enabled', 'pcb_enabled', 'pcb_method', 'hrdf_levy', 'zakat_via_payroll',
        'payment_method', 'net_pay_rounding',
        'requires_approval', 'approver_role', 'lock_after_release',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'pay_day_rule' => PayDayRule::class,
            'pay_day' => 'integer',
            'pay_day_adjustment' => PayDayAdjustment::class,
            'cutoff_day' => 'integer',
            'socso_enabled' => 'boolean',
            'eis_enabled' => 'boolean',
            'pcb_enabled' => 'boolean',
            'pcb_method' => PcbMethod::class,
            'hrdf_levy' => HrdfLevy::class,
            'zakat_via_payroll' => 'boolean',
            'payment_method' => PaymentMethod::class,
            'net_pay_rounding' => NetPayRounding::class,
            'requires_approval' => 'boolean',
            'approver_role' => Role::class,
            'lock_after_release' => 'boolean',
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
            'pay_day_rule' => PayDayRule::FixedDay,
            'pay_day' => 25,
            'pay_day_adjustment' => PayDayAdjustment::PreviousWorkingDay,
            'cutoff_day' => 20,
            'socso_enabled' => true,
            'eis_enabled' => true,
            'pcb_enabled' => true,
            'pcb_method' => PcbMethod::Computerised,
            'hrdf_levy' => HrdfLevy::None,
            'zakat_via_payroll' => true,
            'payment_method' => PaymentMethod::BankTransfer,
            'net_pay_rounding' => NetPayRounding::Sen,
            'requires_approval' => true,
            'approver_role' => Role::Director,
            'lock_after_release' => true,
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
            ->setDescriptionForEvent(fn (): string => 'Updated payroll settings');
    }
}
