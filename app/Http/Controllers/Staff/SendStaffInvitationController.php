<?php

namespace App\Http\Controllers\Staff;

use App\Actions\Staff\SendStaffInvitation;
use App\Http\Controllers\Controller;
use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class SendStaffInvitationController extends Controller
{
    /**
     * Email a staff member their invitation (again), e.g. when they were added without one. Only people
     * who can change the record may send it, and never to someone who has left.
     */
    public function __invoke(Request $request, Employee $employee, SendStaffInvitation $sendInvitation): RedirectResponse
    {
        abort_unless($employee->isEditableBy($request->user()), 403);
        abort_if($employee->hasLeft(), 403);

        return redirect()->route('staff.show', $employee)->with('status', $sendInvitation($employee, $request->user())
            ? __('An invitation to set their password was emailed to :email.', ['email' => $employee->user->email])
            : __('The invitation email couldn’t be sent. Try again later.'));
    }
}
