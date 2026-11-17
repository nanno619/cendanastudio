<?php

namespace App\Http\Controllers\Staff;

use App\Enums\Role;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Support\StaffForm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class EditEmployeeController extends Controller
{
    /**
     * Show the form for a staff record. Only Super Admin can edit a Super Admin's record.
     */
    public function __invoke(Request $request, Employee $employee): View
    {
        abort_if($employee->user->hasRole(Role::SuperAdmin) && ! $request->user()->hasRole(Role::SuperAdmin), 403);

        $employee->load('user.roles');

        return view('staff.form', [
            'employee' => $employee,
            ...StaffForm::options($request->user(), $employee),
        ]);
    }
}
