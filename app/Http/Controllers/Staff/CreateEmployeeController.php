<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Support\StaffForm;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CreateEmployeeController extends Controller
{
    /**
     * Show the form for a new staff member and their sign-in account.
     */
    public function __invoke(Request $request): View
    {
        return view('staff.form', [
            'employee' => new Employee(['nationality' => 'MY', 'identity_type' => 'mykad', 'employment_type' => 'permanent', 'children_count' => 0]),
            ...StaffForm::options($request->user()),
        ]);
    }
}
