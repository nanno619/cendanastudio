<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Welcome email for a new staff member: a link to set their first password, using the password-reset
 * token flow (Fortify's reset page).
 */
class StaffAccountCreated extends Notification
{
    public function __construct(public string $token) {}

    /**
     * @return list<string>
     */
    public function via(User $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(User $notifiable): MailMessage
    {
        $app = config('cendana.brand.workspace');

        return (new MailMessage)
            ->subject(__('Set your password for :app', ['app' => $app]))
            ->greeting(__('Hello :name,', ['name' => $notifiable->name]))
            ->line(__('An account has been created for you on :app. Set your password to sign in.', ['app' => $app]))
            ->action(__('Set password'), route('password.reset', ['token' => $this->token, 'email' => $notifiable->email]))
            ->line(__('This link expires in :count minutes. After that, use “Forgot password” on the sign-in page.', ['count' => config('auth.passwords.users.expire')]));
    }
}
