<?php

namespace App\Models;

use App\Enums\ModuleKey;
use App\Enums\Role;
use Database\Factories\ModuleFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A switchable module (Administrations → Modules). Modules are the ModuleKey cases; a module without a row
 * yet behaves as its defaults (on, open to everyone). When a module is off, its sidebar entries are hidden
 * and routes behind the `module:<key>` middleware return 404; when it's limited to roles, others get 403.
 * Super Admin can always use a module that is on.
 *
 * @property int $id
 * @property string $ulid
 * @property ModuleKey $key Never changes
 * @property string $name_en
 * @property string|null $name_ms Falls back to the English name
 * @property string $icon A name from Module::ICONS
 * @property int $sort_order Sidebar position among modules
 * @property bool $is_enabled
 * @property Collection<int, Role> $allowed_roles Empty = every role; stored in Role order
 */
class Module extends Model
{
    /** @use HasFactory<ModuleFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * Icons that can be chosen for a module (all present in public/icons.svg).
     *
     * @var list<string>
     */
    public const array ICONS = [
        'calendar', 'clock', 'dollar', 'file-text', 'card', 'users', 'user', 'home', 'package', 'truck',
        'chart', 'activity', 'compass', 'layers', 'grid', 'table', 'tag', 'star', 'mail', 'message', 'bell',
        'shield', 'key', 'globe', 'zap', 'image', 'sliders',
    ];

    public const int MAX_NAME_LENGTH = 40;

    /**
     * @var list<string>
     */
    protected $fillable = ['key', 'name_en', 'name_ms', 'icon', 'sort_order', 'is_enabled', 'allowed_roles'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'key' => ModuleKey::class,
            'sort_order' => 'integer',
            'is_enabled' => 'boolean',
            'allowed_roles' => AsEnumCollection::of(Role::class),
        ];
    }

    /**
     * The module's settings: its row, or an unsaved one holding the defaults.
     */
    public static function for(ModuleKey $key): self
    {
        return static::query()->where('key', $key->value)->first() ?? new static(static::defaults($key));
    }

    /**
     * Every module, in sidebar order, keyed by key value. Modules without a row use their defaults.
     *
     * @return Collection<string, self>
     */
    public static function registry(): Collection
    {
        /** @var EloquentCollection<int, self> $saved */
        $saved = static::query()->get()->keyBy(fn (self $module): string => $module->key->value);

        return collect(ModuleKey::cases())
            ->mapWithKeys(fn (ModuleKey $key): array => [$key->value => $saved->get($key->value) ?? new static(static::defaults($key))])
            ->sortBy(fn (self $module): array => [$module->sort_order, $module->key->defaultSortOrder()]);
    }

    /**
     * @return array<string, mixed>
     */
    public static function defaults(ModuleKey $key): array
    {
        return [
            'key' => $key,
            'name_en' => $key->defaultName(),
            'name_ms' => null,
            'icon' => $key->defaultIcon(),
            'sort_order' => $key->defaultSortOrder(),
            'is_enabled' => true,
            'allowed_roles' => [],
        ];
    }

    /**
     * The name in the current language, Malay falling back to English.
     */
    public function label(): string
    {
        return app()->getLocale() === 'ms' && filled($this->name_ms) ? $this->name_ms : $this->name_en;
    }

    public function isRestricted(): bool
    {
        return $this->allowed_roles->isNotEmpty();
    }

    /**
     * Whether this user can use the module: it is on, and it is open to everyone, or the user is Super
     * Admin or holds one of its roles.
     */
    public function isAvailableTo(?User $user): bool
    {
        if (! $this->is_enabled) {
            return false;
        }

        if (! $this->isRestricted()) {
            return true;
        }

        if ($user === null) {
            return false;
        }

        return $user->hasAnyRole([Role::SuperAdmin, ...$this->allowed_roles->all()]);
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
            ->logOnly(['name_en', 'name_ms', 'icon', 'sort_order', 'is_enabled', 'allowed_roles'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => $eventName === 'created' ? 'Created module' : 'Updated module');
    }
}
