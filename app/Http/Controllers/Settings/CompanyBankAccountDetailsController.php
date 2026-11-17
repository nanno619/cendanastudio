<?php

namespace App\Http\Controllers\Settings;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\LookupItem;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class CompanyBankAccountDetailsController extends Controller
{
    /**
     * Show the company's bank account and statutory numbers form and who last changed the company.
     */
    public function __invoke(): View
    {
        $company = Company::current();

        return view('settings.company.bank-account-details', [
            'company' => $company,
            'banks' => LookupItem::options(LookupCategory::Banks),
            'lastChange' => $company->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $company)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
