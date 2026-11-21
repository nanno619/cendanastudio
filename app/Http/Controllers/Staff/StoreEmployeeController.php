<?php

namespace App\Http\Controllers\Staff;

use App\Actions\Staff\SendStaffInvitation;
use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\SaveEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class StoreEmployeeController extends Controller
{
    /**
     * Create the staff member's sign-in account and record together, then, if HR asked for it, email them
     * an invitation to set their password. The account starts with a random password nobody knows; without
     * an invitation (or if mail fails) the record is still saved and one can be sent from their profile.
     */
    public function __invoke(SaveEmployeeRequest $request, SendStaffInvitation $sendInvitation): RedirectResponse
    {
        $employee = DB::transaction(function () use ($request): Employee {
            $user = User::create([...$request->userAttributes(), 'password' => Str::password(40)]);
            $user->assignRole($request->role());

            $employee = $user->employee()->create($request->employeeAttributes());
            $employee->syncEmergencyContacts($request->emergencyContacts());

            return $employee;
        });

        $name = $employee->user->name;

        if (! $request->sendInvitation()) {
            return redirect()->route('staff.show', $employee)->with('status', __(':name was added. No invitation was sent. Use “Send invitation” when they’re ready to sign in.', ['name' => $name]));
        }

        return redirect()->route('staff.show', $employee)->with('status', $sendInvitation($employee, $request->user())
            ? __(':name was added. A link to set their password was emailed to :email.', ['name' => $name, 'email' => $employee->user->email])
            : __(':name was added, but the invitation email couldn’t be sent. Use “Send invitation” to try again.', ['name' => $name]));
    }
}
