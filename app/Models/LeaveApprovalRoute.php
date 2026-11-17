<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\LeaveApprovalRouteFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Who approves leave for applicants with one role (Settings → Leave → Leave Approval). The route with
 * no applicant_role is the default, used by roles without their own. Levels run in order; each is a
 * role (anyone with it may approve) or one user. When no approver is usable, Super Admin decides.
 *
 * @property int $id
 * @property string $ulid
 * @property Role|null $applicant_role null = the default route
 * @property list<array{type: 'role'|'user', value: string|int}> $levels
 */
class LeaveApprovalRoute extends Model
{
    /** @use HasFactory<LeaveApprovalRouteFactory> */
    use HasFactory, HasUlids, LogsActivity;

    public const int MAX_LEVELS = 5;

    /**
     * @var list<string>
     */
    protected $fillable = ['applicant_role', 'levels'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'applicant_role' => Role::class,
            'levels' => 'array',
        ];
    }

    /**
     * The route for an applicant's role, else the default route, else null (Super Admin decides).
     */
    public static function forRole(Role $role): ?self
    {
        return static::query()->where('applicant_role', $role->value)->first()
            ?? static::query()->whereNull('applicant_role')->first();
    }

    public function isDefault(): bool
    {
        return $this->applicant_role === null;
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
                'created' => 'Created leave approval route',
                'deleted' => 'Deleted leave approval route',
                default => 'Updated leave approval route',
            });
    }
}
