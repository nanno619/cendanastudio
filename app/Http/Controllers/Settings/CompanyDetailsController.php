<?php

namespace App\Http\Controllers\Settings;

use App\Enums\LookupCategory;
use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\FileSecuritySetting;
use App\Models\LookupItem;
use Illuminate\View\View;
use Spatie\Activitylog\Models\Activity;

class CompanyDetailsController extends Controller
{
    /**
     * Show the company's basic details form and who last changed it.
     */
    public function __invoke(): View
    {
        $company = Company::current();

        return view('settings.company.basic-details', [
            'company' => $company,
            'states' => LookupItem::options(LookupCategory::MalaysianStates),
            'logoUrl' => $company->exists ? $company->getFirstMediaUrl('logo') : '',
            'maxUploadMb' => FileSecuritySetting::current()->max_file_size_mb,
            'lastChange' => $company->exists
                ? Activity::query()->with('causer')->whereMorphedTo('subject', $company)->latest('id')->first()
                : null,
            'timezone' => config('cendana.timezone'),
        ]);
    }
}
