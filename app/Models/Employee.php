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
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * A staff record (Staff pages). Every staff member signs in with their user; the user holds their name and
 * work email. Lookup columns hold Data Dictionary codes (gender, race, marital status, state, department,
 * position, subsidiary, bank). Status is worked out from the dates in cendana.timezone: left after the last working
 * day, leaving until then, on probation until the probation end date, otherwise active. Staff who have
 * left can't sign in.
 *
 * @property int $id
 * @property string $ulid
 * @property int $user_id
 * @property string|null $preferred_name What they like to be called; the user's name stays the official full name
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
 * @property string|null $subsidiary Data Dictionary Subsidiary code
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
class Employee extends Model implements HasMedia
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, HasUlids, InteractsWithMedia, LogsActivity;

    /**
     * Roles that can view and manage staff records, salaries and IC numbers included.
     *
     * @var list<Role>
     */
    public const array RECORD_MANAGER_ROLES = [Role::SuperAdmin, Role::Admin, Role::Finance];

    public const int MAX_EMERGENCY_CONTACTS = 3;

    /**
     * Staff documents (IC copies, offer letters…) live on the private `local` disk (storage/app/private),
     * never the public one, and are only downloaded through DownloadEmployeeDocumentController.
     */
    public const string DOCUMENTS_DISK = 'local';

    public const int DOCUMENT_NOTE_MAX_LENGTH = 200;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'preferred_name', 'staff_no', 'identity_type', 'identity_no', 'nationality', 'date_of_birth',
        'gender', 'race', 'marital_status', 'children_count', 'spouse_working',
        'phone', 'personal_email', 'address_line_1', 'address_line_2', 'postcode', 'city', 'state',
        'employment_type', 'department', 'position', 'subsidiary', 'reporting_manager_id', 'join_date', 'probation_end_date',
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

    /**
     * @return HasMany<EmergencyContact, $this>
     */
    public function emergencyContacts(): HasMany
    {
        return $this->hasMany(EmergencyContact::class)->orderBy('sort_order');
    }

    /**
     * Save the emergency contacts in the given order: existing contacts are updated in place, extra rows
     * added and leftover contacts removed, so the audit trail only shows real changes.
     *
     * @param  list<array{name: string, relationship: string, phone: string}>  $contacts
     */
    public function syncEmergencyContacts(array $contacts): void
    {
        $existing = $this->emergencyContacts()->get()->values();

        foreach (array_values($contacts) as $position => $contact) {
            $attributes = [...$contact, 'sort_order' => $position + 1];
            $current = $existing->get($position);

            $current ? $current->update($attributes) : $this->emergencyContacts()->create($attributes);
        }

        $existing->slice(count($contacts))->each->delete();
    }

    /**
     * Whether this person may change the record (details, contacts, documents). Only Super Admin can
     * change a Super Admin's record; viewing it is open to every record manager.
     */
    public function isEditableBy(User $actor): bool
    {
        return $actor->hasAnyRole(self::RECORD_MANAGER_ROLES)
            && (! $this->user->hasRole(Role::SuperAdmin) || $actor->hasRole(Role::SuperAdmin));
    }

    /**
     * Whether the staff member still has to fill in their own details (shown as a reminder): a phone
     * number, an address or at least one emergency contact is missing.
     */
    public function needsPersonalDetails(): bool
    {
        return blank($this->phone)
            || blank($this->address_line_1)
            || ! ($this->relationLoaded('emergencyContacts') ? $this->emergencyContacts->isNotEmpty() : $this->emergencyContacts()->exists());
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('documents')->useDisk(self::DOCUMENTS_DISK);
    }

    /**
     * One of this record's documents by its media UUID, or a 404, so URLs never expose media ids.
     */
    public function document(string $uuid): Media
    {
        return $this->getMedia('documents')->firstWhere('uuid', $uuid) ?? abort(404);
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
