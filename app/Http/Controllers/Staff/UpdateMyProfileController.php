<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\UpdateMyProfileRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UpdateMyProfileController extends Controller
{
    /**
     * Save the signed-in staff member's own personal details and emergency contacts.
     */
    public function __invoke(UpdateMyProfileRequest $request): RedirectResponse
    {
        $employee = $request->user()->employee;

        DB::transaction(function () use ($request, $employee): void {
            $employee->update($request->personalDetails());
            $employee->syncEmergencyContacts($request->emergencyContacts());
        });

        return redirect()->route('my-profile.show')->with('status', __('Your details were saved.'));
    }
}
