<x-auth.shell title="{{ __('Reset password') }}" heading="{{ __('Reset password') }}" description="{{ __('Create a new password for your account.') }}">
  <form method="POST" action="{{ route('password.update') }}" class="mt-6 space-y-4">
    @csrf
    <input type="hidden" name="token" value="{{ $request->route('token') }}">
    <x-ui.field label="{{ __('Email') }}" name="email">
      <x-ui.input type="email" name="email" :value="$request->email" autocomplete="email" required />
    </x-ui.field>
    <x-ui.field label="{{ __('New password') }}" name="password">
      <x-ui.input type="password" name="password" autocomplete="new-password" autofocus required />
    </x-ui.field>
    <x-ui.field label="{{ __('Confirm password') }}" name="password_confirmation">
      <x-ui.input type="password" name="password_confirmation" autocomplete="new-password" required />
    </x-ui.field>
    <button type="submit" class="btn btn-primary w-full">{{ __('Reset password') }}</button>
  </form>
</x-auth.shell>
