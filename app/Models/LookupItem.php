<?php

namespace App\Models;

use App\Enums\LookupCategory;
use Database\Factories\LookupItemFactory;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * One Data Dictionary value, e.g. "Selangor" in Malaysian States. Items are deactivated, never deleted,
 * so records that stored the code keep a label.
 */
class LookupItem extends Model
{
    /** @use HasFactory<LookupItemFactory> */
    use HasFactory, LogsActivity;

    /** Badge variants an item may use; null shows plain text. */
    public const TONES = ['primary', 'success', 'info', 'warning', 'danger', 'neutral'];

    /**
     * @var list<string>
     */
    protected $fillable = ['category', 'code', 'label_en', 'label_ms', 'sort_order', 'is_active', 'tone'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'category' => LookupCategory::class,
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Active items of a category in display order, as code => label in the current language, for dropdowns.
     *
     * @return array<string, string>
     */
    public static function options(LookupCategory $category): array
    {
        return static::query()->inCategory($category)->active()->ordered()->get()
            ->mapWithKeys(fn (LookupItem $item): array => [$item->code => $item->label])
            ->all();
    }

    #[Scope]
    protected function inCategory(Builder $query, LookupCategory $category): void
    {
        $query->where('category', $category->value);
    }

    #[Scope]
    protected function active(Builder $query): void
    {
        $query->where('is_active', true);
    }

    #[Scope]
    protected function ordered(Builder $query): void
    {
        $query->orderBy('sort_order')->orderBy('label_en');
    }

    /**
     * The label in the current language, falling back to English when no Malay label is set.
     */
    protected function label(): Attribute
    {
        return Attribute::get(fn (): string => app()->getLocale() === 'ms' && filled($this->label_ms) ? $this->label_ms : $this->label_en);
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['category', 'code', 'label_en', 'label_ms', 'sort_order', 'is_active', 'tone'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->setDescriptionForEvent(fn (string $event): string => match ($event) {
                'created' => "Added {$this->label_en} to the data dictionary",
                default => match (true) {
                    $this->wasChanged('is_active') && ! $this->wasChanged(['label_en', 'label_ms', 'sort_order', 'tone']) => ($this->is_active ? 'Activated' : 'Deactivated')." {$this->label_en} in the data dictionary",
                    default => "Updated {$this->label_en} in the data dictionary",
                },
            });
    }
}
