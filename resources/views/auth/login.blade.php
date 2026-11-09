<x-auth.shell title="{{ __('Sign in') }}" heading="{{ __('Sign in to your account') }}" description="{{ __('Enter your email and password to continue.') }}">
  <form method="POST" action="{{ route('login.store') }}" class="mt-6 space-y-4">
    @csrf
    <x-ui.field label="{{ __('Email') }}" name="email">
      <x-ui.input type="email" name="email" placeholder="{{ __('name@company.com') }}" autocomplete="email" autofocus required />
    </x-ui.field>
    <x-ui.field name="password">
      <div class="flex items-center justify-between">
        <label class="label" for="f-password">{{ __('Password') }}</label>
        <a href="{{ route('password.request') }}" class="text-sm text-primary hover:underline">{{ __('Forgot password?') }}</a>
      </div>
      <x-ui.input type="password" name="password" placeholder="••••••••" autocomplete="current-password" required />
    </x-ui.field>
    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember" value="1" @checked(old('remember'))> {{ __('Remember me on this device') }}</label>
    <x-ui.turnstile />
    <button type="submit" class="btn btn-primary w-full">{{ __('Sign in') }}</button>
  </form>
</x-auth.shell>
