<?php

namespace App\Models;

use App\Enums\FileType;
use Database\Factories\FileSecuritySettingFactory;
use Illuminate\Database\Eloquent\Casts\AsEnumCollection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * The upload policy every upload is validated against (see App\Rules\AllowedUpload). One row only;
 * use current(), which falls back to the defaults until the row is seeded or saved.
 *
 * @property Collection<int, FileType> $allowed_types
 * @property int $max_file_size_mb
 * @property bool $verify_content
 */
class FileSecuritySetting extends Model
{
    /** @use HasFactory<FileSecuritySettingFactory> */
    use HasFactory, LogsActivity;

    /**
     * @var list<string>
     */
    protected $fillable = ['allowed_types', 'max_file_size_mb', 'verify_content'];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        return [
            'allowed_types' => AsEnumCollection::of(FileType::class),
            'max_file_size_mb' => 'integer',
            'verify_content' => 'boolean',
        ];
    }

    /**
     * The saved policy, or an unsaved one holding the defaults.
     */
    public static function current(): self
    {
        return static::query()->first() ?? new static(static::defaults());
    }

    /**
     * @return array{allowed_types: list<FileType>, max_file_size_mb: int, verify_content: bool}
     */
    public static function defaults(): array
    {
        return [
            'allowed_types' => [FileType::Pdf, FileType::Image, FileType::Word, FileType::Excel],
            'max_file_size_mb' => 10,
            'verify_content' => true,
        ];
    }

    /**
     * Every extension the policy allows, e.g. ['pdf', 'jpg', ...].
     *
     * @return list<string>
     */
    public function allowedExtensions(): array
    {
        return $this->allowed_types->flatMap(fn (FileType $type): array => $type->extensions())->values()->all();
    }

    public function allows(FileType $type): bool
    {
        return $this->allowed_types->contains($type);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['allowed_types', 'max_file_size_mb', 'verify_content'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (): string => 'Updated file security settings');
    }
}
