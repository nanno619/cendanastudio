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
                ['maternity', 'Maternity Leave', 'Cuti Bersalin'],
                ['marriage', 'Marriage Leave', 'Cuti Perkahwinan'],
                ['replacement', 'Replacement Leave', 'Cuti Ganti'],
                ['unpaid', 'Unpaid Leave', 'Cuti Tanpa Gaji'],
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
            LookupCategory::Department->value => [
                ['management', 'Management', 'Pengurusan'],
                ['consulting', 'Consulting', 'Perundingan'],
                ['projects', 'Projects', 'Projek'],
                ['finance', 'Finance', 'Kewangan'],
                ['human-resources', 'Human Resources', 'Sumber Manusia'],
                ['sales', 'Sales', 'Jualan'],
                ['support', 'Support', 'Sokongan'],
                ['administration', 'Administration', 'Pentadbiran'],
            ],
            LookupCategory::Position->value => [
                ['director', 'Director', 'Pengarah'],
                ['manager', 'Manager', 'Pengurus'],
                ['project-manager', 'Project Manager', 'Pengurus Projek'],
                ['senior-consultant', 'Senior Consultant', 'Perunding Kanan'],
                ['consultant', 'Consultant', 'Perunding'],
                ['executive', 'Executive', 'Eksekutif'],
                ['accounts-executive', 'Accounts Executive', 'Eksekutif Akaun'],
                ['admin-assistant', 'Administrative Assistant', 'Pembantu Tadbir'],
                ['intern', 'Intern', 'Pelatih'],
            ],
            LookupCategory::Relationship->value => [
                ['spouse', 'Spouse', 'Pasangan'],
                ['father', 'Father', 'Bapa'],
                ['mother', 'Mother', 'Ibu'],
                ['sibling', 'Sibling', 'Adik-beradik'],
                ['child', 'Child', 'Anak'],
                ['relative', 'Relative', 'Saudara'],
                ['guardian', 'Guardian', 'Penjaga'],
                ['friend', 'Friend', 'Rakan'],
                ['other', 'Other', 'Lain-lain'],
            ],
            LookupCategory::Banks->value => [
                ['maybank', 'Maybank', 'Maybank'],
                ['cimb', 'CIMB Bank', 'CIMB Bank'],
                ['public-bank', 'Public Bank', 'Public Bank'],
                ['rhb', 'RHB Bank', 'RHB Bank'],
                ['hong-leong', 'Hong Leong Bank', 'Hong Leong Bank'],
                ['ambank', 'AmBank', 'AmBank'],
                ['bank-islam', 'Bank Islam', 'Bank Islam'],
                ['bank-rakyat', 'Bank Rakyat', 'Bank Rakyat'],
                ['bsn', 'BSN (Bank Simpanan Nasional)', 'BSN (Bank Simpanan Nasional)'],
                ['affin', 'Affin Bank', 'Affin Bank'],
                ['alliance', 'Alliance Bank', 'Alliance Bank'],
                ['bank-muamalat', 'Bank Muamalat', 'Bank Muamalat'],
                ['agrobank', 'Agrobank', 'Agrobank'],
                ['mbsb', 'MBSB Bank', 'MBSB Bank'],
                ['ocbc', 'OCBC Bank', 'OCBC Bank'],
                ['uob', 'UOB', 'UOB'],
                ['hsbc', 'HSBC', 'HSBC'],
                ['standard-chartered', 'Standard Chartered', 'Standard Chartered'],
                ['citibank', 'Citibank', 'Citibank'],
                ['al-rajhi', 'Al Rajhi Bank', 'Al Rajhi Bank'],
                ['kfh', 'Kuwait Finance House', 'Kuwait Finance House'],
            ],
        ];
    }
}
