<?php

namespace Database\Seeders;

use App\Enums\LookupCategory;
use App\Models\LeavePolicy;
use App\Models\LookupItem;
use Illuminate\Database\Seeder;

class LeavePolicySeeder extends Seeder
{
    /**
     * Starter leave policies with the Employment Act 1955 minimums (all environments). Run after
     * LookupSeeder. Only leave types in the Data Dictionary get a policy, and existing policies are
     * left untouched, so changes saved by Super Admin survive re-seeding.
     */
    public function run(): void
    {
        $leaveTypes = LookupItem::query()->inCategory(LookupCategory::LeaveType)->pluck('code')->all();

        activity()->withoutLogging(function () use ($leaveTypes): void {
            foreach ($this->policies() as $leaveType => [$tiers, $isPaid, $requiresAttachment]) {
                if (! in_array($leaveType, $leaveTypes, true)) {
                    continue;
                }

                LeavePolicy::firstOrCreate(['leave_type' => $leaveType], [
                    'entitlements' => array_map(fn (int $minYears, int $days): array => ['min_years' => $minYears, 'days' => $days], array_keys($tiers), $tiers),
                    'is_paid' => $isPaid,
                    'requires_attachment' => $requiresAttachment,
                ]);
            }
        });
    }

    /**
     * Leave type code => [years of service => days, paid, attachment required].
     *
     * @return array<string, array{array<int, int>, bool, bool}>
     */
    private function policies(): array
    {
        return [
            'annual' => [[0 => 8, 2 => 12, 5 => 16], true, false],
            'sick' => [[0 => 14, 2 => 18, 5 => 22], true, true],
            'hospitalization' => [[0 => 60], true, true],
            'maternity' => [[0 => 98], true, true],
            'paternity' => [[0 => 7], true, true],
            'compassionate' => [[0 => 3], true, false],
            'marriage' => [[0 => 3], true, true],
            'replacement' => [[], true, false],
            'unpaid' => [[], false, false],
        ];
    }
}
