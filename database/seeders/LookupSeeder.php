<?php

namespace Database\Seeders;

use App\Enums\LookupCategory;
use App\Models\LookupItem;
use Illuminate\Database\Seeder;

class LookupSeeder extends Seeder
{
    /**
     * Starter Data Dictionary values (all environments). Existing items are left untouched,
     * so labels, order and status edited by Super Admin survive re-seeding.
     */
    public function run(): void
    {
        // Seeding is setup, not user activity, so it stays out of the audit trail even when run on its own.
        activity()->withoutLogging(function (): void {
            foreach ($this->items() as $category => $items) {
                foreach (array_values($items) as $position => [$code, $labelEn, $labelMs]) {
                    LookupItem::firstOrCreate(
                        ['category' => $category, 'code' => $code],
                        ['label_en' => $labelEn, 'label_ms' => $labelMs, 'sort_order' => ($position + 1) * 10, 'is_active' => true],
                    );
                }
            }
        });
    }

    /**
     * @return array<string, list<array{string, string, string}>>
     */
    private function items(): array
    {
        return [
            LookupCategory::MalaysianStates->value => [
                ['johor', 'Johor', 'Johor'],
                ['kedah', 'Kedah', 'Kedah'],
                ['kelantan', 'Kelantan', 'Kelantan'],
                ['melaka', 'Melaka', 'Melaka'],
                ['negeri-sembilan', 'Negeri Sembilan', 'Negeri Sembilan'],
                ['pahang', 'Pahang', 'Pahang'],
                ['penang', 'Penang', 'Pulau Pinang'],
                ['perak', 'Perak', 'Perak'],
                ['perlis', 'Perlis', 'Perlis'],
                ['sabah', 'Sabah', 'Sabah'],
                ['sarawak', 'Sarawak', 'Sarawak'],
                ['selangor', 'Selangor', 'Selangor'],
                ['terengganu', 'Terengganu', 'Terengganu'],
                ['kuala-lumpur', 'Federal Territory of Kuala Lumpur', 'Wilayah Persekutuan Kuala Lumpur'],
                ['labuan', 'Federal Territory of Labuan', 'Wilayah Persekutuan Labuan'],
                ['putrajaya', 'Federal Territory of Putrajaya', 'Wilayah Persekutuan Putrajaya'],
            ],
        ];
    }
}
