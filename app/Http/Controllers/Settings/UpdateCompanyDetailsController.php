<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateCompanyDetailsRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;

class UpdateCompanyDetailsController extends Controller
{
    /**
     * Save the company's basic details and logo, creating its single row on first save.
     */
    public function __invoke(UpdateCompanyDetailsRequest $request): RedirectResponse
    {
        $company = Company::query()->firstOrNew();
        $company->fill($request->companyDetails())->save();

        if ($request->hasFile('logo')) {
            $company->addMediaFromRequest('logo')->toMediaCollection('logo');
        } elseif ($request->boolean('remove_logo')) {
            $company->clearMediaCollection('logo');
        }

        return redirect()->route('settings.company.basic-details')
            ->with('status', __('Company details were saved.'));
    }
}
