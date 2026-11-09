<x-auth.shell title="{{ __('Forgot password') }}" heading="{{ __('Forgot password?') }}" description="{{ __('Enter your account email and we\'ll send you a link to reset your password.') }}">
  <form method="POST" action="{{ route('password.email') }}" class="mt-6 space-y-4">
    @csrf
    <x-ui.field label="{{ __('Email') }}" name="email">
      <x-ui.input type="email" name="email" placeholder="nama@email.com" autocomplete="email" autofocus required />
    </x-ui.field>
    <x-ui.turnstile />
    <button type="submit" class="btn btn-primary w-full">{{ __('Send reset link') }}</button>
  </form>
  <p class="mt-8 text-center text-sm text-muted-foreground"><a href="{{ route('login') }}" class="font-medium text-primary hover:underline">{{ __('Back to sign in') }}</a></p>
</x-auth.shell>
