<?php

namespace App\Models;

use Database\Factories\ClientContactFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * A contact person at a client, edited on the client form.
 *
 * @property int $id
 * @property string $ulid
 * @property int $client_id
 * @property string $name
 * @property string|null $position
 * @property string|null $phone
 * @property string|null $email
 * @property int $sort_order 1 = main contact
 */
class ClientContact extends Model
{
    /** @use HasFactory<ClientContactFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * @var list<string>
     */
    protected $fillable = ['name', 'position', 'phone', 'email', 'sort_order'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    /**
     * @return BelongsTo<Client, $this>
     */
    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
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
            ->logOnly(['name', 'position', 'phone', 'email', 'sort_order'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'Added client contact',
                'deleted' => 'Removed client contact',
                default => 'Updated client contact',
            });
    }
}
