<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Failed;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Spatie\Activitylog\Support\ActivityLogger;

/**
 * Writes sign-in, sign-out, failed sign-in and lockout events to the audit trail.
 */
class RecordAuthenticationActivity
{
    public function __construct(private Request $request) {}

    public function handleLogin(Login $event): void
    {
        $this->logger()->causedBy($event->user)->performedOn($event->user)->event('login')
            ->withProperties($this->context(['remember' => $event->remember ?: null]))
            ->log('Signed in');
    }

    public function handleLogout(Logout $event): void
    {
        if (! $event->user instanceof User) {
            return;
        }

        $this->logger()->causedBy($event->user)->performedOn($event->user)->event('logout')
            ->withProperties($this->context())
            ->log('Signed out');
    }

    public function handleFailed(Failed $event): void
    {
        $logger = $this->logger()->causedByAnonymous()->event('failed-login')
            ->withProperties($this->context(['email' => $event->credentials[Fortify::username()] ?? null]));

        if ($event->user instanceof User) {
            $logger->performedOn($event->user);
        }

        $logger->log($event->user ? 'Failed sign-in: wrong password' : 'Failed sign-in: unknown email');
    }

    public function handleLockout(Lockout $event): void
    {
        $this->logger()->causedByAnonymous()->event('lockout')
            ->withProperties($this->context(['email' => $event->request->input(Fortify::username())]))
            ->log('Sign-in locked after too many attempts');
    }

    private function logger(): ActivityLogger
    {
        return activity();
    }

    /**
     * Request details stored with every authentication entry.
     *
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>
     */
    private function context(array $extra = []): array
    {
        return array_filter([
            ...$extra,
            'ip' => $this->request->ip(),
            'user_agent' => Str::limit((string) $this->request->userAgent(), 255, ''),
        ], fn (mixed $value): bool => filled($value));
    }
}
