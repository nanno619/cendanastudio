<?php

namespace App\Http\Controllers\Settings;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LookupItem;
use Illuminate\View\View;

class CompanyBankAccountDetailsController extends Controller
{
    /**
     * Show the company's bank account and statutory numbers form and who last changed them.
     */
    public function __invoke(): View
    {
        $company = Company::current();

        return view('settings.company.bank-account-details', [
            'company' => $company,
            'banks' => LookupItem::options(LookupCategory::Banks),
            'lastChange' => $company->latestChangeTo(Company::BANK_ACCOUNT_FIELDS),
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
