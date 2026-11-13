<?php

namespace App\Models;

use Database\Factories\CompanyFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

/**
 * The app's own company (Settings → Company → Basic Details). One row only; use current(),
 * which returns an unsaved company until the details are first saved.
 *
 * @property int $id
 * @property string $ulid
 * @property string $name
 * @property string|null $registration_no SSM 12-digit number, e.g. 202301012345
 * @property string|null $old_registration_no Pre-2019 SSM number, e.g. 1234567-A
 * @property string|null $email
 * @property string|null $phone
 * @property string|null $website
 * @property string|null $address_line_1
 * @property string|null $address_line_2
 * @property string|null $postcode
 * @property string|null $city
 * @property string|null $state LookupItem code in LookupCategory::MalaysianStates
 * @property string $country ISO 3166-1 alpha-2 code
 * @property string|null $epf_no KWSP employer number
 * @property string|null $socso_no PERKESO employer code
 * @property string|null $eis_no SIP employer code
 * @property string|null $income_tax_no LHDN employer E-number
 * @property string|null $zakat_no
 */
class Company extends Model implements HasMedia
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, HasUlids, InteractsWithMedia, LogsActivity;

    /**
     * @var list<string>
     */
    protected $fillable = [
        'name', 'registration_no', 'old_registration_no',
        'email', 'phone', 'website',
        'address_line_1', 'address_line_2', 'postcode', 'city', 'state', 'country',
        'epf_no', 'socso_no', 'eis_no', 'income_tax_no', 'zakat_no',
    ];

    /**
     * @var array<string, mixed>
     */
    protected $attributes = [
        'country' => 'MY',
    ];

    /**
     * The saved company, or an unsaved one until the details are first saved.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static;
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

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')->singleFile();
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly($this->fillable)
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $eventName): string => match ($eventName) {
                'created' => 'Created company details',
                'deleted' => 'Deleted company details',
                default => 'Updated company details',
            });
    }
}
