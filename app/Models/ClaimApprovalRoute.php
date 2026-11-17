<?php

namespace App\Models;

use App\Enums\Role;
use Database\Factories\ClaimApprovalRouteFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Who approves claims for applicants with one role (Settings → Claim → Approval Workflows). The route
 * with no applicant_role is the default, used by roles without their own. Levels run in order; each is a
 * role (anyone with it may approve) or one user, and may apply only to claims above an amount. Level 1
 * always applies. When no approver is usable, Super Admin decides.
 *
 * @property int $id
 * @property string $ulid
 * @property Role|null $applicant_role null = the default route
 * @property list<array{type: 'role'|'user', value: string|int, above: string|null}> $levels
 */
class ClaimApprovalRoute extends Model
{
    /** @use HasFactory<ClaimApprovalRouteFactory> */
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
     * The levels a claim of this total goes through, in order: those without an amount, and those whose
     * amount the total is above.
     *
     * @return list<array{type: 'role'|'user', value: string|int, above: string|null}>
     */
    public function levelsFor(string|float $total): array
    {
        return array_values(array_filter(
            $this->levels,
            fn (array $level): bool => ($level['above'] ?? null) === null || (float) $total > (float) $level['above'],
        ));
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
                'created' => 'Created claim approval route',
                'deleted' => 'Deleted claim approval route',
                default => 'Updated claim approval route',
            });
    }
}
