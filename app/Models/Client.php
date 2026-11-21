<?php

namespace App\Models;

use App\Enums\ClientStatus;
use App\Enums\Role;
use Database\Factories\ClientFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A client company (CRM), with its contact persons and a status: Active, Closure (no longer a client) or
 * Blacklist (don't do business with them), the last two with a reason. Clients aren't deleted.
 *
 * @property int $id
 * @property string $ulid
 * @property string $name
 * @property string|null $registration_no SSM number, 12 digits
 * @property string|null $phone
 * @property string|null $email
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $postcode
 * @property string|null $city
 * @property string|null $state LookupCategory::MalaysianStates code
 * @property string|null $notes
 * @property ClientStatus $status
 * @property string|null $status_reason Why the client was closed or blacklisted; null while active
 */
class Client extends Model
{
    /** @use HasFactory<ClientFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Roles that can view CRM clients.
     *
     * @var list<Role>
     */
    public const array VIEWER_ROLES = [Role::SuperAdmin, Role::Director, Role::ProjectManager, Role::Sales, Role::Admin, Role::Finance];

    /**
     * Roles that can add and edit clients.
     *
     * @var list<Role>
     */
    public const array EDITOR_ROLES = [Role::SuperAdmin, Role::Director, Role::ProjectManager, Role::Sales];

    public const int STATUS_REASON_MAX_LENGTH = 500;

    public const int MAX_CONTACTS = 5;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name', 'registration_no', 'phone', 'email',
        'address_line_1', 'address_line_2', 'postcode', 'city', 'state', 'notes', 'status', 'status_reason',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = ['status' => 'active'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return ['status' => ClientStatus::class];
    }

    /**
     * @return HasMany<ClientContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(ClientContact::class)->orderBy('sort_order');
    }

    /**
     * Save the contact persons in form order: existing rows are updated by position, new ones added and
     * leftovers removed, so only real changes are audited.
     *
     * @param  list<array{name: string, position: string|null, phone: string|null, email: string|null}>  $contacts
     */
    public function syncContacts(array $contacts): void
    {
        $existing = $this->contacts()->get()->values();

        foreach (array_values($contacts) as $position => $contact) {
            $attributes = [...$contact, 'sort_order' => $position + 1];
            $current = $existing->get($position);

            $current ? $current->update($attributes) : $this->contacts()->create($attributes);
        }

        $existing->slice(count($contacts))->each->delete();
    }

    public static function isViewableBy(User $user): bool
    {
        return $user->hasAnyRole(self::VIEWER_ROLES);
    }

    public static function isEditableBy(User $user): bool
    {
        return $user->hasAnyRole(self::EDITOR_ROLES);
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
            ->setDescriptionForEvent(fn (string $eventName): string => $eventName === 'created' ? 'Created client' : 'Updated client');
    }
}
