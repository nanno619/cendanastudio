<x-layouts.admin title="{{ __('Claim Approval Workflows') }}" group="{{ __('Settings') }}">
<div class="flex flex-wrap items-end justify-between gap-4">
    <x-ui.page-header title="{{ __('Claim Approval Workflows') }}" description="{{ __('Who approves claims, by the claimant’s role. Levels run in order and can apply only to larger claims; when no approver is available, Super Admin decides.') }}" />
    @if ($canAdd)
        <a href="{{ route('settings.claim.approval.routes.create') }}" class="btn btn-primary"><x-ui.icon name="plus" />{{ __('Add route') }}</a>
    @endif
</div>

@if (session('status'))
    <x-ui.alert variant="success" role="status">{{ session('status') }}</x-ui.alert>
@endif

@unless ($hasDefault)
    <x-ui.alert variant="warning">{{ __('Without a default route, roles without their own route go to Super Admin.') }}</x-ui.alert>
@endunless

<section class="card" aria-labelledby="routes-heading">
    <div class="flex flex-col gap-1.5 p-5 sm:px-6">
        <h2 id="routes-heading" class="card-title">{{ __('Approval routes') }}</h2>
        <p class="card-desc">{{ __('Roles without their own route use the default route, “Everyone else”.') }}</p>
    </div>

    @if ($routes->isEmpty())
        <div class="border-t">
            <x-ui.table-state state="empty" title="{{ __('No approval routes') }}" description="{{ __('Add a default route first, then routes for roles that need different approvers.') }}" />
        </div>
    @else
        <div class="overflow-x-auto border-t">
            <table class="table min-w-180">
                <thead>
                    <tr>
                        <th scope="col" class="w-24"><span class="sr-only">{{ __('Actions') }}</span></th>
                        <th scope="col">{{ __('Applies to') }}</th>
                        <th scope="col">{{ __('Approvers, in order') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($routes as $route)
                        @php $appliesTo = $route->isDefault() ? __('Everyone else') : __($route->applicant_role->value); @endphp
                        <tr>
                            <td data-actions class="whitespace-nowrap">
                                <a href="{{ route('settings.claim.approval.routes.edit', $route) }}" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-tip="{{ __('Edit') }}" data-tip-pos="right" aria-label="{{ __('Edit :label', ['label' => $appliesTo]) }}"><x-ui.icon name="edit" /></a>
                                @unless ($route->isDefault())
                                    <button type="button" class="btn btn-ghost btn-icon btn-sm text-muted-foreground" data-modal-open="#delete-route-{{ $route->ulid }}" data-tip="{{ __('Delete') }}" data-tip-pos="right" aria-label="{{ __('Delete route for :label', ['label' => $appliesTo]) }}"><x-ui.icon name="trash" /></button>
                                @endunless
                            </td>
                            <td class="font-medium">
                                {{ $appliesTo }}
                                @if ($route->isDefault())
                                    <x-ui.badge variant="neutral" class="ml-1">{{ __('Default') }}</x-ui.badge>
                                @endif
                            </td>
                            <td>
                                <ol class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    @foreach ($route->levels as $position => $level)
                                        <li class="flex items-center gap-2">
                                            @if ($position > 0)<x-ui.icon name="arrow-right" class="h-3.5 w-3.5 text-muted-foreground" />@endif
                                            <span class="tabular-nums text-muted-foreground">{{ $position + 1 }}.</span>
                                            @if ($level['type'] === 'role')
                                                <span>{{ __($level['value']) }} <span class="text-xs text-muted-foreground">({{ __('role') }})</span></span>
                                            @elseif ($users->has($level['value']))
                                                <span>{{ $users[$level['value']] }} <span class="text-xs text-muted-foreground">({{ __('user') }})</span></span>
                                            @else
                                                <x-ui.badge variant="danger">{{ __('User no longer exists') }}</x-ui.badge>
                                            @endif
                                            @if ($level['above'] ?? null)
                                                <span class="text-xs text-muted-foreground">{{ __('above RM :amount', ['amount' => number_format((float) $level['above'], 2)]) }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                </ol>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</section>

@foreach ($routes as $route)
    @unless ($route->isDefault())
        <x-ui.confirm-action id="delete-route-{{ $route->ulid }}" title="{{ __('Delete this route?') }}"
            description="{{ __(':role will use the default route instead.', ['role' => __($route->applicant_role->value)]) }}"
            :action="route('settings.claim.approval.routes.destroy', $route)" />
    @endunless
@endforeach

</x-layouts.admin>
