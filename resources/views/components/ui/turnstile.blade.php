{{-- Cloudflare Turnstile bot check. Renders nothing until TURNSTILE_SITE_KEY/SECRET_KEY are set; verified by VerifyTurnstile. --}}
@if (\App\Http\Middleware\VerifyTurnstile::enabled())
    <div class="cf-turnstile min-h-[65px]" data-sitekey="{{ config('services.turnstile.site_key') }}" data-language="{{ app()->getLocale() }}" data-size="flexible" data-theme="auto"></div>
    <script>
        (function (widget) {
            var theme = document.documentElement.dataset.theme;
            if (theme === 'light' || theme === 'dark') widget.dataset.theme = theme;
        })(document.currentScript.previousElementSibling);
    </script>
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>
    @endonce
@endif
