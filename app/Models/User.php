<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    use LogsActivity {
        buildChanges as buildChangesFromTrait;
    }

    /** Logged fields whose values are replaced before they reach the audit trail. */
    private const MASKED_FIELDS = ['password'];

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Up to two uppercase initials of the user's name, for avatars.
     */
    protected function initials(): Attribute
    {
        return Attribute::get(fn (): string => Str::of($this->name)
            ->explode(' ')
            ->filter()
            ->take(2)
            ->map(fn (string $part): string => Str::upper(Str::substr($part, 0, 1)))
            ->implode(''));
    }

    /**
     * Record account creation, edits and deletion in the audit trail.
     */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['name', 'email', 'email_verified_at', 'password'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event): string => match ($event) {
                'created' => 'Created user account',
                'deleted' => 'Deleted user account',
                default => $this->onlyPasswordChanged() ? 'Changed password' : 'Updated user account',
            });
    }

    /**
     * Never store password hashes in the audit trail; keep only the fact that they changed.
     *
     * @return array<string, array<string, mixed>>
     */
    protected function buildChanges(string $processingEvent): array
    {
        $changes = $this->buildChangesFromTrait($processingEvent);

        foreach (['attributes', 'old'] as $side) {
            foreach (self::MASKED_FIELDS as $field) {
                if (array_key_exists($field, $changes[$side] ?? [])) {
                    $changes[$side][$field] = '[hidden]';
                }
            }
        }

        return $changes;
    }

    private function onlyPasswordChanged(): bool
    {
        return $this->wasChanged('password') && ! $this->wasChanged(['name', 'email', 'email_verified_at']);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
