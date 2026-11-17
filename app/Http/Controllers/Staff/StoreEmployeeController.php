<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\SaveEmployeeRequest;
use App\Models\Employee;
use App\Models\User;
use App\Notifications\StaffAccountCreated;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class StoreEmployeeController extends Controller
{
    /**
     * Create the staff member's sign-in account and record together, then email them a link to set their
     * password. The account starts with a random password nobody knows; if mail fails, the record is still
     * saved and they can use “Forgot password”.
     */
    public function __invoke(SaveEmployeeRequest $request): RedirectResponse
    {
        $employee = DB::transaction(function () use ($request): Employee {
            $user = User::create([...$request->userAttributes(), 'password' => Str::password(40)]);
            $user->assignRole($request->role());

            $employee = $user->employee()->create($request->employeeAttributes());
            $employee->syncEmergencyContacts($request->emergencyContacts());

            return $employee;
        });

        $linkSent = rescue(function () use ($employee): bool {
            $employee->user->notify(new StaffAccountCreated(Password::broker()->createToken($employee->user)));

            return true;
        }, false);

        return redirect()->route('staff.show', $employee)->with('status', $linkSent
            ? __(':name was added. A link to set their password was emailed to :email.', ['name' => $employee->user->name, 'email' => $employee->user->email])
            : __(':name was added, but the password email couldn’t be sent. They can use “Forgot password” on the sign-in page.', ['name' => $employee->user->name]));
    }
}
