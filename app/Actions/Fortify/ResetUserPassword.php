<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\ResetsUserPasswords;

class ResetUserPassword implements ResetsUserPasswords
{
    use PasswordValidationRules;

    /**
     * Validate and reset the user's forgotten password.
     *
     * @param  array<string, string>  $input
     *
     * @throws ValidationException
     */
    public function reset(User $user, array $input): void
    {
        Validator::make($input, [
            'password' => $this->passwordRules(),
        ])->validate();

        // Nobody is signed in during a reset, so record it against the user instead of letting the model log it as "System".
        activity()->withoutLogging(fn () => $user->forceFill([
            'password' => Hash::make($input['password']),
        ])->save());

        activity()->causedBy($user)->performedOn($user)->event('password-reset')
            ->withChanges(['attributes' => ['password' => '[hidden]'], 'old' => ['password' => '[hidden]']])
            ->log('Reset password with an email link');
    }
}
