<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateCompanyBankAccountDetailsRequest;
use App\Models\Company;
use Illuminate\Http\RedirectResponse;

class UpdateCompanyBankAccountDetailsController extends Controller
{
    /**
     * Save the company's bank account and statutory numbers. The company row must exist,
     * since Basic Details holds the required name.
     */
    public function __invoke(UpdateCompanyBankAccountDetailsRequest $request): RedirectResponse
    {
        $company = Company::query()->first();

        if ($company === null) {
            return back()->withErrors(['company' => __('Save the company’s basic details first.')]);
        }

        $company->update($request->validated());

        return redirect()->route('settings.company.bank-account-details')
            ->with('status', __('Bank account details were saved.'));
    }
}
