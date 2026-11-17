<x-layouts.admin title="{{ __('Modules') }}" group="{{ __('Administrations') }}">
<x-ui.page-header title="{{ __('Modules') }}" description="{{ __('Switch modules on or off, choose who can use them, and set their name, icon and order in the sidebar.') }}" />

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

<section class="card" aria-labelledby="modules-heading">
    <div class="flex flex-col gap-1.5 p-5 sm:px-6">
        <h2 id="modules-heading" class="card-title">{{ __('Modules') }}</h2>
        <p class="card-desc">{{ __('A module that is off disappears from the sidebar, with its settings, and its pages can’t be opened.') }}</p>
    </div>

    <div class="overflow-x-auto border-t">
        <table class="table min-w-180">
            <thead>
                <tr>
                    <th scope="col" class="w-16"><span class="sr-only">{{ __('Actions') }}</span></th>
                    <th scope="col">{{ __('Module') }}</th>
                    <th scope="col">{{ __('Status') }}</th>
                    <th scope="col">{{ __('Who can use it') }}</th>
                    <th scope="col" class="text-right">{{ __('Order') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($modules as $module)
                    <tr>
                        <td data-actions>
                            <a href="{{ route('administrations.modules.edit', $module->key) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Edit') }}" data-tip-pos="right" aria-label="{{ __('Edit :label', ['label' => $module->label()]) }}"><x-ui.icon name="edit" /></a>
                        </td>
                        <td>
                            <div class="flex items-center gap-3">
                                <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-md bg-soft text-soft-foreground"><x-ui.icon :name="$module->icon" class="h-4 w-4" /></span>
                                <div class="min-w-0">
                                    <p class="font-medium">{{ $module->label() }}</p>
                                    <p class="text-xs text-muted-foreground">{{ __($module->key->description()) }}</p>
                                </div>
                            </div>
                        </td>
                        <td>
                            @if ($module->is_enabled)
                                <x-ui.badge variant="success">{{ __('On') }}</x-ui.badge>
                            @else
                                <x-ui.badge variant="neutral">{{ __('Off') }}</x-ui.badge>
                            @endif
                        </td>
                        <td>
                            @if ($module->isRestricted())
                                {{ $module->allowed_roles->map(fn ($role) => __($role->value))->implode(', ') }}
                                <span class="text-xs text-muted-foreground">{{ __('and Super Admin') }}</span>
                            @else
                                {{ __('Everyone') }}
                            @endif
                        </td>
                        <td class="text-right tabular-nums">{{ $module->sort_order }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</section>
</x-layouts.admin>
