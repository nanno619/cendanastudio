<?php

namespace App\Http\Controllers\Settings;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LookupItem;
use App\Models\PayslipSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\File;
use Symfony\Component\HttpFoundation\Response;

class PreviewPayslipController extends Controller
{
    /**
     * Stream a payslip PDF for a sample staff member, laid out with the saved payslip options and the
     * company's details, in the payslip language.
     */
    public function __invoke(): Response
    {
        $setting = PayslipSetting::current();
        $company = Company::current();

        App::setLocale($setting->language);

        return Pdf::loadView('pdf.payslip', [
            'setting' => $setting,
            'company' => $company,
            'companyName' => $company->name ?? config('cendana.brand.workspace'),
            'companyState' => $company->state ? LookupItem::options(LookupCategory::MalaysianStates)[$company->state] ?? null : null,
            'logo' => $setting->show_company_logo ? $this->logoDataUri($company) : null,
            'payslip' => $this->samplePayslip(),
        ])->stream('payslip-preview.pdf');
    }

    /**
     * The logo inlined as a data URI, so DomPDF needs no file access.
     */
    private function logoDataUri(Company $company): ?string
    {
        $logo = $company->exists ? $company->getFirstMedia('logo') : null;

        if (! $logo || ! File::exists($logo->getPath())) {
            return null;
        }

        return 'data:'.$logo->mime_type.';base64,'.base64_encode(File::get($logo->getPath()));
    }

    /**
     * Made-up figures for the preview; the Payroll module will supply real ones.
     *
     * @return array<string, mixed>
     */
    private function samplePayslip(): array
    {
        return [
            'period' => now(config('cendana.timezone'))->startOfMonth(),
            'name' => 'Nur Aisyah binti Ahmad',
            'staff_no' => 'CS-0042',
            'identity' => '900101-14-5678',
            'position' => 'Project Consultant',
            'department' => 'Consulting',
            'bank' => 'Maybank',
            'bank_account' => '512345678901',
            'epf_no' => '12345678',
            'socso_no' => '900101145678',
            'tax_no' => 'IG 12345678090',
            'earnings' => [
                ['label' => 'Basic salary', 'amount' => 4500.00],
                ['label' => 'Fixed allowance', 'amount' => 300.00],
                ['label' => 'Overtime (6 hours)', 'amount' => 186.98],
            ],
            'deductions' => [
                ['label' => 'EPF (employee)', 'amount' => 528.00],
                ['label' => 'SOCSO (employee)', 'amount' => 24.75],
                ['label' => 'EIS (employee)', 'amount' => 9.90],
                ['label' => 'PCB (income tax)', 'amount' => 85.00],
            ],
            'employer_contributions' => [
                ['label' => 'EPF (employer)', 'amount' => 624.00],
                ['label' => 'SOCSO (employer)', 'amount' => 86.65],
                ['label' => 'EIS (employer)', 'amount' => 9.90],
            ],
            'year_to_date' => [
                ['label' => 'Gross pay', 'amount' => 54862.40],
                ['label' => 'EPF (employee)', 'amount' => 5808.00],
                ['label' => 'PCB (income tax)', 'amount' => 935.00],
            ],
            'leave_balance' => [
                ['label' => 'Annual leave', 'days' => 9.5],
                ['label' => 'Sick leave', 'days' => 12],
            ],
        ];
    }
}
