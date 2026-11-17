<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Http\Requests\Staff\SaveEmployeeRequest;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

class UpdateEmployeeController extends Controller
{
    /**
     * Save a staff record and its sign-in account's name, email and role.
     */
    public function __invoke(SaveEmployeeRequest $request, Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($request, $employee): void {
            $employee->user->update($request->userAttributes());

            if (! $employee->user->hasRole($request->role())) {
                $employee->user->syncRoles($request->role());
            }

            $employee->update($request->employeeAttributes());
        });

        return redirect()->route('staff.show', $employee)
            ->with('status', __(':name was updated.', ['name' => $employee->user->name]));
    }
}
