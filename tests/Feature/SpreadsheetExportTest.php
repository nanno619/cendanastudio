<?php

namespace Tests\Feature;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class SpreadsheetExportTest extends TestCase
{
    public function test_exports_rows_to_csv(): void
    {
        $export = new class implements FromArray, WithHeadings
        {
            public function headings(): array
            {
                return ['Code', 'Title'];
            }

            public function array(): array
            {
                return [['ENT-001', 'Onboarding guide']];
            }
        };

        $csv = Excel::raw($export, \Maatwebsite\Excel\Excel::CSV);

        $this->assertStringContainsString('"Code","Title"', $csv);
        $this->assertStringContainsString('"ENT-001","Onboarding guide"', $csv);
    }
}
