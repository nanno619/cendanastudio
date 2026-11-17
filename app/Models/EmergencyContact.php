<?php

namespace App\Models;

use Database\Factories\EmergencyContactFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Someone to call in an emergency for a staff member, edited on the staff form.
 *
 * @property int $id
 * @property string $ulid
 * @property int $employee_id
 * @property string $name
 * @property string $relationship LookupCategory::Relationship code
 * @property string $phone
 * @property int $sort_order 1 = call first
 */
class EmergencyContact extends Model
{
    /** @use HasFactory<EmergencyContactFactory> */
    use HasFactory, HasUlids, LogsActivity;

    /**
     * @var list<string>
     */
    protected $fillable = ['name', 'relationship', 'phone', 'sort_order'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return ['sort_order' => 'integer'];
    }

    /**
     * @return BelongsTo<Employee, $this>
     */
    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
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
            ->logOnly(['name', 'relationship', 'phone', 'sort_order'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'Added emergency contact',
                'deleted' => 'Removed emergency contact',
                default => 'Updated emergency contact',
            });
    }
}
