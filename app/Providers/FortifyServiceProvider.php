<?php

namespace App\Providers;

use App\Actions\Fortify\RejectStaffWhoHaveLeft;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Exceptions\ThrottleRequestsException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Actions\AttemptToAuthenticate;
use Laravel\Fortify\Actions\CanonicalizeUsername;
use Laravel\Fortify\Actions\EnsureLoginIsNotThrottled;
use Laravel\Fortify\Actions\PrepareAuthenticatedSession;
use Laravel\Fortify\Contracts\RedirectsIfTwoFactorAuthenticatable;
use Laravel\Fortify\Features;
use Laravel\Fortify\Fortify;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        // Fortify's default sign-in pipeline, plus a step refusing staff whose last working day has passed.
        Fortify::authenticateThrough(fn (Request $request): array => array_filter([
            config('fortify.limiters.login') ? null : EnsureLoginIsNotThrottled::class,
            config('fortify.lowercase_usernames') ? CanonicalizeUsername::class : null,
            Features::enabled(Features::twoFactorAuthentication()) ? RedirectsIfTwoFactorAuthenticatable::class : null,
            RejectStaffWhoHaveLeft::class,
            AttemptToAuthenticate::class,
            PrepareAuthenticatedSession::class,
        ]));

        Fortify::loginView('auth.login');
        Fortify::requestPasswordResetLinkView('auth.forgot-password');
        Fortify::resetPasswordView('auth.reset-password');

        RateLimiter::for('login', function (Request $request) {
            $throttleKey = Str::transliterate(Str::lower($request->input(Fortify::username())).'|'.$request->ip());

            // The named limiter answers before Fortify's own lockout check, so fire Lockout here for the audit trail.
            return Limit::perMinute(5)->by($throttleKey)->response(function (Request $request, array $headers): never {
                event(new Lockout($request));

                throw new ThrottleRequestsException('Too Many Attempts.', null, $headers);
            });
        });
    }
}
