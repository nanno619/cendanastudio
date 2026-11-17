<?php

namespace App\Models;

use App\Enums\ClaimPaymentMethod;
use Carbon\CarbonInterface;
use Database\Factories\ClaimSettingFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Company-wide claim rules (Settings → Claim → General). One row only; use current(), which holds the
 * defaults until the settings are first saved. Claim types are Data Dictionary items; who approves is
 * on Claim → Approval Workflows.
 *
 * @property int $id
 * @property string $ulid
 * @property int $submission_deadline_days Days after the expense date; 0 = no deadline
 * @property string $receipt_required_above RM; 0 = a receipt is always needed
 * @property bool $allow_resubmission Rejected claims can be corrected and submitted again
 * @property bool $mileage_enabled Staff can claim distance driven in their own vehicle
 * @property string|null $mileage_rate RM per km, set only while mileage claims are on
 * @property ClaimPaymentMethod $payment_method
 */
class ClaimSetting extends Model
{
    /** @use HasFactory<ClaimSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    public const int MAX_SUBMISSION_DEADLINE_DAYS = 365;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'submission_deadline_days', 'receipt_required_above', 'allow_resubmission',
        'mileage_enabled', 'mileage_rate', 'payment_method',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'submission_deadline_days' => 'integer',
            'receipt_required_above' => 'decimal:2',
            'allow_resubmission' => 'boolean',
            'mileage_enabled' => 'boolean',
            'mileage_rate' => 'decimal:2',
            'payment_method' => ClaimPaymentMethod::class,
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
            'submission_deadline_days' => 30,
            'receipt_required_above' => '0.00',
            'allow_resubmission' => true,
            'mileage_enabled' => false,
            'mileage_rate' => null,
            'payment_method' => ClaimPaymentMethod::WithPayroll,
        ];
    }

    /**
     * Whether a claim for an expense on this date can still be submitted on the given day.
     */
    public function acceptsExpenseDate(CarbonInterface $expenseDate, CarbonInterface $today): bool
    {
        if ($expenseDate->startOfDay()->greaterThan($today->startOfDay())) {
            return false;
        }

        return $this->submission_deadline_days === 0
            || $expenseDate->startOfDay()->diffInDays($today->startOfDay()) <= $this->submission_deadline_days;
    }

    /**
     * Whether an expense of this amount needs a receipt.
     */
    public function needsReceipt(string|float $amount): bool
    {
        $threshold = (float) $this->receipt_required_above;

        return $threshold === 0.0 || (float) $amount > $threshold;
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
            ->setDescriptionForEvent(fn (): string => 'Updated claim settings');
    }
}
