<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use App\Enums\Role;
use Carbon\CarbonImmutable;
use Database\Factories\ProjectFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A client project (Projects module). Overseers (Super Admin, Director, Project Manager, Admin) see every
 * project; anyone else sees the projects they manage. Super Admin and Director edit any project, a
 * Project Manager only the ones they manage.
 *
 * @property int $id
 * @property string $ulid
 * @property string $code Unique, capitals, digits and dashes, e.g. PRJ-2026-001
 * @property string $name
 * @property int $client_id
 * @property int|null $project_manager_id An employees id
 * @property ProjectStatus $status
 * @property CarbonImmutable|null $start_date
 * @property CarbonImmutable|null $end_date
 * @property string|null $description
 */
class Project extends Model
{
    /** @use HasFactory<ProjectFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Roles that see every project.
     *
     * @var list<Role>
     */
    public const array OVERSEER_ROLES = [Role::SuperAdmin, Role::Director, Role::ProjectManager, Role::Admin];

    /**
     * Roles that edit any project.
     *
     * @var list<Role>
     */
    public const array EDITOR_ROLES = [Role::SuperAdmin, Role::Director];

    /**
     * Roles that can add projects; a Project Manager becomes the manager of the projects they add.
     *
     * @var list<Role>
     */
    public const array CREATOR_ROLES = [Role::SuperAdmin, Role::Director, Role::ProjectManager];

    /**
     * @var list<string>
     */
    protected $fillable = ['code', 'name', 'client_id', 'project_manager_id', 'status', 'start_date', 'end_date', 'description'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'status' => ProjectStatus::class,
            'start_date' => 'immutable_date',
            'end_date' => 'immutable_date',
        ];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function projectManager(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'project_manager_id');
    }

    /**
     * Only the projects this person may see.
     */
    #[Scope]
    protected function visibleTo(Builder $query, User $user): void
    {
        if ($user->hasAnyRole(self::OVERSEER_ROLES)) {
            return;
        }

        $query->where('project_manager_id', $user->employee?->id ?? 0);
    }

    public function isVisibleTo(User $user): bool
    {
        return $user->hasAnyRole(self::OVERSEER_ROLES) || $this->isManagedBy($user);
    }

    public function isEditableBy(User $user): bool
    {
        return $user->hasAnyRole(self::EDITOR_ROLES) || ($user->hasRole(Role::ProjectManager) && $this->isManagedBy($user));
    }

    public function isManagedBy(User $user): bool
    {
        return $this->project_manager_id !== null && $this->project_manager_id === $user->employee?->id;
    }

    public static function isCreatableBy(User $user): bool
    {
        return $user->hasAnyRole(self::CREATOR_ROLES);
    }

    /**
     * Whether this person can choose the project manager (otherwise a Project Manager manages what they add).
     */
    public static function canAssignManager(User $user): bool
    {
        return $user->hasAnyRole(self::EDITOR_ROLES);
    }

    /**
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
            ->setDescriptionForEvent(fn (string $eventName): string => $eventName === 'created' ? 'Created project' : 'Updated project');
    }
}
