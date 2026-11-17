<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\VisitSettingFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Rules for work away from the office (Settings → Hadir → Visits). One row only; use current(), which
 * holds the defaults until the settings are first saved. A visit is checked in and out at the client or
 * site with the device's location, whatever the office geofence on Hadir → General says.
 *
 * @property int $id
 * @property string $ulid
 * @property bool $visits_enabled
 * @property Collection<int, Role> $allowed_roles Roles that can log visits, in Role order
 * @property bool $counts_as_attendance A visit check-in counts as clocking in for the day
 * @property bool $require_client Client or site name
 * @property bool $require_purpose
 * @property bool $require_photo Photo at check-in
 * @property bool $requires_approval
 * @property Role|null $approver_role Set only while approval is on
 * @property bool $suggest_mileage_claim Offer a mileage claim for the distance, when Claim → General allows mileage
 */
class VisitSetting extends Model
{
    /** @use HasFactory<VisitSettingFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'visits_enabled', 'allowed_roles', 'counts_as_attendance',
        'require_client', 'require_purpose', 'require_photo',
        'requires_approval', 'approver_role', 'suggest_mileage_claim',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'visits_enabled' => 'boolean',
            'allowed_roles' => AsEnumCollection::of(Role::class),
            'counts_as_attendance' => 'boolean',
            'require_client' => 'boolean',
            'require_purpose' => 'boolean',
            'require_photo' => 'boolean',
            'requires_approval' => 'boolean',
            'approver_role' => Role::class,
            'suggest_mileage_claim' => 'boolean',
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
     * Roles that usually work at client sites can log visits.
     *
     * @return array<string, mixed>
     */
    public static function defaults(): array
    {
        return [
            'visits_enabled' => true,
            'allowed_roles' => [Role::ProjectManager, Role::Consultant, Role::Support, Role::Sales],
            'counts_as_attendance' => true,
            'require_client' => true,
            'require_purpose' => true,
            'require_photo' => false,
            'requires_approval' => false,
            'approver_role' => null,
            'suggest_mileage_claim' => false,
        ];
    }

    /**
     * Whether someone with these roles can log a visit. Super Admin always can.
     *
     * @param  iterable<Role>  $roles
     */
    public function allowsAnyOf(iterable $roles): bool
    {
        if (! $this->visits_enabled) {
            return false;
        }

        foreach ($roles as $role) {
            if ($role === Role::SuperAdmin || $this->allowed_roles->contains($role)) {
                return true;
            }
        }

        return false;
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
            ->setDescriptionForEvent(fn (): string => 'Updated visit settings');
    }
}
