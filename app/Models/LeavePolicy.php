<?php

namespace App\Models;

use Database\Factories\LeavePolicyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * The rules for one leave type (Settings → Leave → Leave Policies). Entitlement tiers are stored
 * sorted by years of service; no tiers means the leave has no fixed entitlement (e.g. unpaid leave).
 *
 * @property int $id
 * @property string $ulid
 * @property string $leave_type LookupItem code in LookupCategory::LeaveType
 * @property bool $is_paid
 * @property bool $requires_attachment
 * @property list<array{min_years: int, days: float}> $entitlements
 * @property bool $allow_carry_forward
 * @property string|null $carry_forward_max_days Set only while carry forward is on
 * @property int|null $carry_forward_expiry_months Carried days expire this many months into the new leave year; null = never
 */
class LeavePolicy extends Model
{
    /** @use HasFactory<LeavePolicyFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'leave_type', 'is_paid', 'requires_attachment', 'entitlements',
        'allow_carry_forward', 'carry_forward_max_days', 'carry_forward_expiry_months',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'is_paid' => true,
        'requires_attachment' => false,
        'entitlements' => '[]',
        'allow_carry_forward' => false,
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'is_paid' => 'boolean',
            'requires_attachment' => 'boolean',
            'entitlements' => 'array',
            'allow_carry_forward' => 'boolean',
            'carry_forward_max_days' => 'decimal:1',
            'carry_forward_expiry_months' => 'integer',
        ];
    }

    /**
     * The saved policy for a leave type, or an unsaved one until it is set up.
     */
    public static function for(string $leaveType): self
    {
        return static::query()->where('leave_type', $leaveType)->first() ?? new static(['leave_type' => $leaveType]);
    }

    /**
     * Days a year for someone with this many completed years of service, or null when the leave has no fixed entitlement.
     */
    public function entitlementFor(int $yearsOfService): ?float
    {
        $tier = collect($this->entitlements)->last(fn (array $tier): bool => $tier['min_years'] <= $yearsOfService);

        return $tier === null ? null : (float) $tier['days'];
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
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'Created leave policy',
                'deleted' => 'Deleted leave policy',
                default => 'Updated leave policy',
            });
    }
}
