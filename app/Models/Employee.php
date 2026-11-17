<?php

namespace App\Models;

use App\Enums\EmployeeStatus;
use App\Enums\EmploymentType;
use App\Enums\IdentityType;
use App\Enums\LeavingReason;
use App\Enums\Role;
use Carbon\CarbonImmutable;
use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A staff record (Staff pages). Every staff member signs in with their user; the user holds their name and
 * work email. Lookup columns hold Data Dictionary codes (gender, race, marital status, state, department,
 * position, bank). Status is worked out from the dates in cendana.timezone: left after the last working
 * day, leaving until then, on probation until the probation end date, otherwise active. Staff who have
 * left can't sign in.
 *
 * @property int $id
 * @property string $ulid
 * @property int $user_id
 * @property string $staff_no
 * @property IdentityType $identity_type
 * @property string $identity_no MyKad digits only, or the passport number in capitals
 * @property string $nationality ISO 3166-1 alpha-2
 * @property CarbonImmutable $date_of_birth Taken from the MyKad number when there is one
 * @property string|null $gender
 * @property string|null $race
 * @property string|null $marital_status
 * @property int $children_count For PCB reliefs
 * @property bool|null $spouse_working For PCB reliefs; null when not married
 * @property string|null $phone
 * @property string|null $personal_email
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $postcode
 * @property string|null $city
 * @property string|null $state
 * @property EmploymentType $employment_type
 * @property string|null $department
 * @property string|null $position
 * @property int|null $reporting_manager_id
 * @property CarbonImmutable $join_date
 * @property CarbonImmutable|null $probation_end_date
 * @property bool $is_manual_worker
 * @property CarbonImmutable|null $last_working_date
 * @property LeavingReason|null $leaving_reason Set together with last_working_date
 * @property string $basic_salary Monthly, in RM
 * @property string|null $bank
 * @property string|null $bank_account_no Digits only
 * @property string|null $epf_no
 * @property string|null $socso_no
 * @property string|null $income_tax_no
 * @property string|null $epf_elected_rate Employee EPF rate above the statutory one, when EPF settings allow it
 */
class Employee extends Model
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Roles that can view and manage staff records, salaries and IC numbers included.
     *
     * @var list<Role>
     */
    public const array RECORD_MANAGER_ROLES = [Role::SuperAdmin, Role::Admin, Role::Finance];

    /**
     * @var list<string>
     */
    protected $fillable = [
        'staff_no', 'identity_type', 'identity_no', 'nationality', 'date_of_birth',
        'gender', 'race', 'marital_status', 'children_count', 'spouse_working',
        'phone', 'personal_email', 'address_line_1', 'address_line_2', 'postcode', 'city', 'state',
        'employment_type', 'department', 'position', 'reporting_manager_id', 'join_date', 'probation_end_date',
        'is_manual_worker', 'last_working_date', 'leaving_reason',
        'basic_salary', 'bank', 'bank_account_no',
        'epf_no', 'socso_no', 'income_tax_no', 'epf_elected_rate',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'identity_type' => IdentityType::class,
            'date_of_birth' => 'immutable_date',
            'children_count' => 'integer',
            'spouse_working' => 'boolean',
            'employment_type' => EmploymentType::class,
            'join_date' => 'immutable_date',
            'probation_end_date' => 'immutable_date',
            'is_manual_worker' => 'boolean',
            'last_working_date' => 'immutable_date',
            'leaving_reason' => LeavingReason::class,
            'basic_salary' => 'decimal:2',
            'epf_elected_rate' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function reportingManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'reporting_manager_id');
    }

    /**
     * @return HasMany<Employee, $this>
     */
    public function directReports(): HasMany
    {
        return $this->hasMany(Employee::class, 'reporting_manager_id');
    }

    public function status(?CarbonImmutable $today = null): EmployeeStatus
    {
        $today ??= self::today();

        return match (true) {
            $this->last_working_date !== null && $this->last_working_date->lessThan($today) => EmployeeStatus::Left,
            $this->last_working_date !== null => EmployeeStatus::Leaving,
            $this->probation_end_date !== null && $this->probation_end_date->greaterThanOrEqualTo($today) => EmployeeStatus::Probation,
            default => EmployeeStatus::Active,
        };
    }

    /**
     * Whether the last working day has passed; such staff can't sign in.
     */
    public function hasLeft(?CarbonImmutable $today = null): bool
    {
        return $this->status($today) === EmployeeStatus::Left;
    }

    public function age(?CarbonImmutable $today = null): int
    {
        return (int) $this->date_of_birth->diffInYears($today ?? self::today());
    }

    /**
     * Narrow to one status, or to everyone still employed (not left) when null.
     *
     * @param  Builder<Employee>  $query
     */
    #[Scope]
    protected function withStatus(Builder $query, ?EmployeeStatus $status): void
    {
        $today = self::today()->toDateString();
        $notLeft = fn (Builder $query) => $query->whereNull('last_working_date')->orWhere('last_working_date', '>=', $today);

        match ($status) {
            null => $query->where($notLeft),
            EmployeeStatus::Left => $query->where('last_working_date', '<', $today),
            EmployeeStatus::Leaving => $query->where('last_working_date', '>=', $today),
            EmployeeStatus::Probation => $query->whereNull('last_working_date')->where('probation_end_date', '>=', $today),
            EmployeeStatus::Active => $query->whereNull('last_working_date')
                ->where(fn (Builder $query) => $query->whereNull('probation_end_date')->orWhere('probation_end_date', '<', $today)),
        };
    }

    /**
     * Today in the zone pages display, which is what the dates mean.
     */
    public static function today(): CarbonImmutable
    {
        return CarbonImmutable::now(config('cendana.timezone'))->startOfDay();
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
            ->setDescriptionForEvent(fn (string $eventName): string => $eventName === 'created' ? 'Created staff record' : 'Updated staff record');
    }
}
