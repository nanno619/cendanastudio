<?php

namespace App\Actions\Staff;

use App\Models\Employee;
use App\Models\User;
use App\Notifications\StaffAccountCreated;
use Illuminate\Support\Facades\Password;

/**
 * Emails a staff member their invitation: a link to set their password, using a fresh password-broker
 * token (any earlier link stops working). Mail failures are reported, not thrown, so the caller can tell
 * HR; only invitations that were sent are audited, against the staff record.
 */
class SendStaffInvitation
{
    public function __invoke(Employee $employee, User $sender): bool
    {
        $sent = rescue(function () use ($employee): bool {
            $employee->user->notify(new StaffAccountCreated(Password::broker()->createToken($employee->user)));

            return true;
        }, false);

        if ($sent) {
            activity()
                ->causedBy($sender)
                ->performedOn($employee)
                ->event('invitation-sent')
                ->withProperties(['email' => $employee->user->email])
                ->log('Sent staff invitation');
        }

        return $sent;
    }
}
