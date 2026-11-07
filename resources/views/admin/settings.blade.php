<x-layouts.admin title="{{ __('Account settings') }}" group="{{ __('Pages') }}">
@php $securityTab = $errors->updatePassword->any() || session('status') === 'password-updated'; @endphp
<x-ui.page-header title="{{ __('Account settings') }}" description="{{ __('Manage your profile, security and notification preferences.') }}" />

<div data-tabs>
  <div class="tabs-underline" role="tablist" aria-label="{{ __('Settings') }}">
    <button type="button" role="tab" class="tab" id="st-1" aria-selected="{{ $securityTab ? 'false' : 'true' }}" @if ($securityTab) tabindex="-1" @endif aria-controls="sp-1"><x-ui.icon name="user" />{{ __('Profile') }}</button>
    <button type="button" role="tab" class="tab" id="st-2" aria-selected="{{ $securityTab ? 'true' : 'false' }}" @unless ($securityTab) tabindex="-1" @endunless aria-controls="sp-2"><x-ui.icon name="lock" />{{ __('Security') }}</button>
    <button type="button" role="tab" class="tab" id="st-3" aria-selected="false" tabindex="-1" aria-controls="sp-3"><x-ui.icon name="bell" />{{ __('Notifications') }}</button>
  </div>

  <!-- Profile -->
  <div id="sp-1" role="tabpanel" aria-labelledby="st-1" class="grid gap-4 pt-6 lg:grid-cols-3" @if ($securityTab) hidden @endif>
    <div class="card h-fit p-6 text-center">
      <span class="avatar avatar-xl mx-auto">{{ auth()->user()->initials }}</span>
      <h2 class="mt-3 font-semibold">{{ auth()->user()->name }}</h2>
      <p class="text-sm text-muted-foreground">{{ auth()->user()->email }}</p>
      <div class="mt-3 flex justify-center gap-2"><span class="badge badge-success badge-dot">{{ __('Active') }}</span><span class="badge badge-soft">Admin</span></div>
      <button type="button" class="btn btn-outline mt-5 w-full"><x-ui.icon name="upload" />{{ __('Change photo') }}</button>
    </div>
    <form method="POST" action="{{ route('user-profile-information.update') }}" class="card lg:col-span-2">
      @csrf
      @method('PUT')
      <div class="card-header"><h2 class="card-title">{{ __('Profile information') }}</h2><p class="card-desc">{{ __('Update your name and email.') }}</p></div>
      <div class="card-content grid gap-4 sm:grid-cols-2">
        @if (session('status') === 'profile-information-updated')
          <x-ui.alert variant="success" class="sm:col-span-2">{{ __('Profile updated.') }}</x-ui.alert>
        @endif
        <x-ui.field label="{{ __('Name') }}" name="name" class="sm:col-span-2">
          <x-ui.input name="name" :value="auth()->user()->name" autocomplete="name" required :invalid="$errors->updateProfileInformation->has('name')" />
          @if ($errors->updateProfileInformation->has('name')) <p class="field-error">{{ $errors->updateProfileInformation->first('name') }}</p> @endif
        </x-ui.field>
        <x-ui.field label="{{ __('Email') }}" name="email" class="sm:col-span-2">
          <x-ui.input type="email" name="email" :value="auth()->user()->email" autocomplete="email" required :invalid="$errors->updateProfileInformation->has('email')" />
          @if ($errors->updateProfileInformation->has('email')) <p class="field-error">{{ $errors->updateProfileInformation->first('email') }}</p> @endif
        </x-ui.field>
      </div>
      <div class="card-footer justify-end"><button type="submit" class="btn btn-primary">{{ __('Save changes') }}</button></div>
    </form>
  </div>

  <!-- Security -->
  <div id="sp-2" role="tabpanel" aria-labelledby="st-2" class="space-y-4 pt-6" @unless ($securityTab) hidden @endunless>
    <form method="POST" action="{{ route('user-password.update') }}" class="card">
      @csrf
      @method('PUT')
      <div class="card-header"><h2 class="card-title">{{ __('Change password') }}</h2><p class="card-desc">{{ __('Use at least 8 characters.') }}</p></div>
      <div class="card-content grid max-w-xl gap-4">
        @if (session('status') === 'password-updated')
          <x-ui.alert variant="success">{{ __('Password updated.') }}</x-ui.alert>
        @endif
        <x-ui.field label="{{ __('Current password') }}" name="current_password">
          <x-ui.input type="password" name="current_password" autocomplete="current-password" required :invalid="$errors->updatePassword->has('current_password')" />
          @if ($errors->updatePassword->has('current_password')) <p class="field-error">{{ $errors->updatePassword->first('current_password') }}</p> @endif
        </x-ui.field>
        <x-ui.field label="{{ __('New password') }}" name="password">
          <x-ui.input type="password" name="password" autocomplete="new-password" required :invalid="$errors->updatePassword->has('password')" />
          @if ($errors->updatePassword->has('password')) <p class="field-error">{{ $errors->updatePassword->first('password') }}</p> @endif
        </x-ui.field>
        <x-ui.field label="{{ __('Confirm password') }}" name="password_confirmation">
          <x-ui.input type="password" name="password_confirmation" autocomplete="new-password" required :invalid="$errors->updatePassword->has('password_confirmation')" />
          @if ($errors->updatePassword->has('password_confirmation')) <p class="field-error">{{ $errors->updatePassword->first('password_confirmation') }}</p> @endif
        </x-ui.field>
      </div>
      <div class="card-footer justify-end"><button type="submit" class="btn btn-primary">{{ __('Update') }}</button></div>
    </form>
    <div class="card p-6">
      <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-start gap-4"><span class="avatar avatar-success avatar-lg"><x-ui.icon name="shield" class="h-5 w-5" /></span><div><h2 class="font-semibold">{{ __('Two-step verification') }}</h2><p class="text-sm text-muted-foreground">{{ __('Add a layer of security with an authenticator app.') }}</p></div></div>
        <label class="switch"><input type="checkbox" class="peer sr-only" checked aria-label="{{ __('Two-step verification') }}"><span class="switch-track"></span></label>
      </div>
    </div>
    <div class="rounded-xl border border-danger/30 bg-danger-soft p-6">
      <h2 class="font-semibold text-danger">{{ __('Danger zone') }}</h2>
      <p class="mt-1 text-sm text-muted-foreground">{{ __('Deleting your account permanently removes all of its data.') }}</p>
      <button type="button" class="btn btn-destructive mt-4">{{ __('Delete account') }}</button>
    </div>
  </div>

  <!-- Notifikasi -->
  <div id="sp-3" role="tabpanel" aria-labelledby="st-3" class="pt-6" hidden>
    <div class="card">
      <div class="card-header"><h2 class="card-title">{{ __('Notification preferences') }}</h2><p class="card-desc">{{ __('Choose which updates you want to receive.') }}</p></div>
      <ul class="divide-y border-t">
        <li class="flex items-center justify-between gap-4 px-6 py-4"><div><p class="font-medium">{{ __('New orders') }}</p><p class="text-sm text-muted-foreground">{{ __('Tell me whenever a new order comes in.') }}</p></div><label class="switch"><input type="checkbox" class="peer sr-only" checked aria-label="{{ __('New orders') }}"><span class="switch-track"></span></label></li>
        <li class="flex items-center justify-between gap-4 px-6 py-4"><div><p class="font-medium">{{ __('Low stock') }}</p><p class="text-sm text-muted-foreground">{{ __('Alert when stock drops below the minimum.') }}</p></div><label class="switch"><input type="checkbox" class="peer sr-only" checked aria-label="{{ __('Low stock') }}"><span class="switch-track"></span></label></li>
        <li class="flex items-center justify-between gap-4 px-6 py-4"><div><p class="font-medium">{{ __('Weekly summary') }}</p><p class="text-sm text-muted-foreground">{{ __('Performance report sent every Monday morning.') }}</p></div><label class="switch"><input type="checkbox" class="peer sr-only" aria-label="{{ __('Weekly summary') }}"><span class="switch-track"></span></label></li>
        <li class="flex items-center justify-between gap-4 px-6 py-4"><div><p class="font-medium">{{ __('Promotions and news') }}</p><p class="text-sm text-muted-foreground">{{ __('News about features and offers.') }}</p></div><label class="switch"><input type="checkbox" class="peer sr-only" aria-label="{{ __('Promotions and news') }}"><span class="switch-track"></span></label></li>
      </ul>
    </div>
  </div>
</div>
</x-layouts.admin>
