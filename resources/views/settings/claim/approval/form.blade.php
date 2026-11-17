@php
    $isNew = ! $approvalRoute->exists;
    $appliesTo = $isNew ? null : ($approvalRoute->isDefault() ? __('Everyone else') : __($approvalRoute->applicant_role->value));
    $title = $isNew ? __('Add approval route') : __('Approval route: :role', ['role' => $appliesTo]);
    $current = old('levels', collect($approvalRoute->levels)->map(fn ($level) => ['approver' => $level['type'].':'.$level['value'], 'above' => $level['above'] ?? null])->all());
@endphp
<x-layouts.admin :title="$title" group="{{ __('Claim Approval Workflows') }}">
<div>
    <a href="{{ route('settings.claim.approval.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ __('Back to Claim Approval Workflows') }}
    </a>
    <h1 class="text-2xl font-semibold tracking-tight">{{ $title }}</h1>
    <p class="mt-1 text-muted-foreground">{{ __('Claims go to level 1 first, then each next level after approval. A level with an amount is only used for claims above it.') }}</p>
</div>

<form method="POST" action="{{ $isNew ? route('settings.claim.approval.routes.store') : route('settings.claim.approval.routes.update', $approvalRoute) }}" class="space-y-4">
    @csrf
    @unless ($isNew) @method('PUT') @endunless

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Applies to') }}</h2>
        </div>
        <div class="card-content">
            @if ($isNew)
                <x-ui.field label="{{ __('Applicant’s role') }}" name="applicant_role" class="max-w-sm">
                    <x-ui.select name="applicant_role">
                        @if ($defaultAvailable)
                            <option value="">{{ __('Everyone else (default)') }}</option>
                        @endif
                        @foreach ($availableRoles as $role)
                            <option value="{{ $role->value }}" @selected(old('applicant_role') === $role->value)>{{ __($role->value) }}</option>
                        @endforeach
                    </x-ui.select>
                </x-ui.field>
            @else
                <p class="font-medium">{{ $appliesTo }}</p>
                <p class="field-help mt-1">{{ __('A route’s role can’t change. Delete it and add a new one instead.') }}</p>
            @endif
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Approval levels') }}</h2>
            <p class="card-desc">{{ __('Choose a role (anyone with it can approve) or a specific user, and optionally an amount: the level is then used only for claims above it. Leave unused levels blank.') }}</p>
        </div>
        <div class="card-content space-y-3">
            @error('levels')<x-ui.alert variant="danger">{{ $message }}</x-ui.alert>@enderror
            @for ($row = 0; $row < \App\Models\ClaimApprovalRoute::MAX_LEVELS; $row++)
                @php
                    $selected = (string) ($current[$row]['approver'] ?? '');
                    $above = $current[$row]['above'] ?? null;
                @endphp
                <div class="grid items-start gap-2 sm:grid-cols-[6rem_minmax(0,24rem)_minmax(0,12rem)]">
                    <label for="f-level-{{ $row }}" class="pt-2 text-sm font-medium">{{ __('Level :n', ['n' => $row + 1]) }}</label>
                    <div>
                        <x-ui.select id="f-level-{{ $row }}" name="levels[{{ $row }}][approver]" :invalid="$errors->has('levels.'.$row.'.approver')">
                            <option value="">{{ __('— None —') }}</option>
                            <optgroup label="{{ __('Roles') }}">
                                @foreach (\App\Enums\Role::cases() as $role)
                                    <option value="role:{{ $role->value }}" @selected($selected === 'role:'.$role->value)>{{ __($role->value) }}</option>
                                @endforeach
                            </optgroup>
                            <optgroup label="{{ __('Users') }}">
                                @foreach ($users as $user)
                                    <option value="user:{{ $user->id }}" @selected($selected === 'user:'.$user->id)>{{ $user->name }}</option>
                                @endforeach
                            </optgroup>
                        </x-ui.select>
                        @if (str_starts_with($selected, 'user:') && ! $users->contains('id', (int) substr($selected, 5)))
                            <p class="field-error">{{ __('This user no longer exists. Choose another approver.') }}</p>
                        @endif
                        @error('levels.'.$row.'.approver')<p class="field-error">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        @if ($row === 0)
                            <p class="pt-2 text-sm text-muted-foreground">{{ __('Every claim') }}</p>
                            @error('levels.0.above')<p class="field-error">{{ $message }}</p>@enderror
                        @else
                            <x-ui.input name="levels[{{ $row }}][above]" type="number" min="0.01" step="0.01" :value="$above" addon="RM" class="tabular-nums" placeholder="{{ __('Any amount') }}" aria-label="{{ __('Level :n: only for claims above', ['n' => $row + 1]) }}" :invalid="$errors->has('levels.'.$row.'.above')" />
                            @error('levels.'.$row.'.above')<p class="field-error">{{ $message }}</p>@enderror
                        @endif
                    </div>
                </div>
            @endfor
        </div>
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <p class="text-sm text-muted-foreground">
            @if (! $isNew && $lastChange)
                {{ __('Last changed by :name on :date.', ['name' => $lastChange->causer?->name ?? __('System'), 'date' => $lastChange->created_at->timezone($timezone)->translatedFormat('j M Y, g:i A')]) }}
                <a href="{{ route('administrations.audit-trail.show', $lastChange) }}" class="font-medium text-foreground underline">{{ __('View in Audit Trail') }}</a>
            @endif
        </p>
        <button type="submit" class="btn btn-primary">{{ $isNew ? __('Add route') : __('Save changes') }}</button>
    </div>
</form>
</x-layouts.admin>
