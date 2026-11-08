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
                foreach (array_values($items) as $position => $item) {
                    [$code, $labelEn, $labelMs] = $item;

                    LookupItem::firstOrCreate(
                        ['category' => $category, 'code' => $code],
                        ['label_en' => $labelEn, 'label_ms' => $labelMs, 'sort_order' => ($position + 1) * 10, 'is_active' => true, 'tone' => $item[3] ?? null],
                    );
                }
            }
        });
    }

    /**
     * @return array<string, list<array{0: string, 1: string, 2: string, 3?: string}>>
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
            LookupCategory::Race->value => [
                ['malay', 'Malay', 'Melayu'],
                ['chinese', 'Chinese', 'Cina'],
                ['indian', 'Indian', 'India'],
                ['bumiputera-sabah', 'Bumiputera Sabah', 'Bumiputera Sabah'],
                ['bumiputera-sarawak', 'Bumiputera Sarawak', 'Bumiputera Sarawak'],
                ['others', 'Others', 'Lain-lain'],
            ],
            LookupCategory::MaritalStatus->value => [
                ['single', 'Single', 'Bujang'],
                ['married', 'Married', 'Berkahwin'],
                ['divorced', 'Divorced', 'Bercerai'],
                ['widowed', 'Widowed', 'Balu / Duda'],
            ],
            LookupCategory::LeaveType->value => [
                ['annual', 'Annual Leave', 'Cuti Tahunan'],
                ['sick', 'Sick Leave', 'Cuti Sakit'],
                ['hospitalization', 'Hospitalization Leave', 'Cuti Hospital'],
                ['paternity', 'Paternity Leave', 'Cuti Paterniti'],
                ['compassionate', 'Compassionate Leave', 'Cuti Ihsan'],
            ],
            LookupCategory::ClaimType->value => [
                ['fuel', 'Fuel', 'Bahan Api'],
                ['stationery', 'Stationery', 'Alat Tulis'],
                ['stamping', 'Stamping', 'Duti Setem'],
                ['meal', 'Meal', 'Makan'],
                ['toll', 'Toll', 'Tol'],
                ['accommodation', 'Accommodation', 'Penginapan'],
                ['medical', 'Medical', 'Perubatan'],
            ],
            LookupCategory::Gender->value => [
                ['male', 'Male', 'Lelaki'],
                ['female', 'Female', 'Perempuan'],
            ],
            LookupCategory::Statuses->value => [
                ['draft', 'Draft', 'Draf', 'neutral'],
                ['submitted', 'Submitted', 'Dihantar', 'info'],
                ['approved', 'Approved', 'Diluluskan', 'success'],
                ['rejected', 'Rejected', 'Ditolak', 'danger'],
                ['cancelled', 'Cancelled', 'Dibatalkan', 'warning'],
            ],
        ];
    }
}
