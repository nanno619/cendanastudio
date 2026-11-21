@php
    $editing = $client->exists;
    $title = $editing ? __('Edit :label', ['label' => $client->name]) : __('Add client');
    $contacts = old('contacts', $editing ? $client->contacts->map(fn ($contact) => $contact->only(['name', 'position', 'phone', 'email']))->all() : []);
@endphp
<x-layouts.admin :title="$title" group="{{ __('CRM') }}">
<div>
    <a href="{{ $editing ? route('crm.clients.show', $client) : route('crm.index') }}" class="mb-3 inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground">
        <x-ui.icon name="arrow-left" class="h-4 w-4" />{{ $editing ? __('Back to :name', ['name' => $client->name]) : __('Back to CRM') }}
    </a>
    <x-ui.page-header :title="$title" description="{{ __('Fields marked * are required.') }}" />
</div>

<form method="POST" action="{{ $editing ? route('crm.clients.update', $client) : route('crm.clients.store') }}" class="space-y-4">
    @csrf
    @if ($editing) @method('PUT') @endif

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Company') }}</h2>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Company name') }}" name="name" required class="md:col-span-2">
                <x-ui.input name="name" :value="$client->name" maxlength="150" required />
            </x-ui.field>
            <x-ui.field label="{{ __('SSM registration number') }}" name="registration_no" help="{{ __('12 digits, e.g. 202301012345.') }}">
                <x-ui.input name="registration_no" :value="$client->registration_no" class="font-mono" inputmode="numeric" maxlength="14" autocomplete="off" />
            </x-ui.field>
            <x-ui.field label="{{ __('Phone') }}" name="phone">
                <x-ui.input name="phone" type="tel" :value="$client->phone" maxlength="20" placeholder="03-1234 5678" />
            </x-ui.field>
            <x-ui.field label="{{ __('Email') }}" name="email">
                <x-ui.input name="email" type="email" :value="$client->email" />
            </x-ui.field>
        </div>
        <div class="card-content grid gap-5 border-t pt-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Address line 1') }}" name="address_line_1" class="md:col-span-3">
                <x-ui.input name="address_line_1" :value="$client->address_line_1" maxlength="255" />
            </x-ui.field>
            <x-ui.field label="{{ __('Address line 2') }}" name="address_line_2" class="md:col-span-3">
                <x-ui.input name="address_line_2" :value="$client->address_line_2" maxlength="255" />
            </x-ui.field>
            <x-ui.field label="{{ __('Postcode') }}" name="postcode">
                <x-ui.input name="postcode" :value="$client->postcode" inputmode="numeric" maxlength="5" class="tabular-nums" />
            </x-ui.field>
            <x-ui.field label="{{ __('City') }}" name="city">
                <x-ui.input name="city" :value="$client->city" maxlength="100" />
            </x-ui.field>
            <x-ui.field label="{{ __('State') }}" name="state">
                <x-ui.select name="state" :value="$client->state" :options="$states" include-blank />
            </x-ui.field>
        </div>
        <div class="card-content border-t pt-5">
            <x-ui.field label="{{ __('Notes') }}" name="notes">
                <x-ui.textarea name="notes" :value="$client->notes" rows="3" maxlength="2000" />
            </x-ui.field>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Status') }}</h2>
            <p class="card-desc">{{ __('Closure and Blacklist need a reason. Changes are recorded in the audit trail.') }}</p>
        </div>
        <div class="card-content grid gap-5 md:grid-cols-3">
            <x-ui.field label="{{ __('Status') }}" name="status" required>
                <x-ui.select name="status" :value="$client->status->value" :options="$statuses" required />
            </x-ui.field>
            {{-- Shown only for Closure or Blacklist (resources/js/admin/conditional-fields.js). --}}
            <div class="md:col-span-2" data-show-when="status" data-show-values="{{ \App\Enums\ClientStatus::Closure->value }},{{ \App\Enums\ClientStatus::Blacklist->value }}" @unless (\App\Enums\ClientStatus::tryFrom((string) old('status', $client->status->value))?->needsReason()) hidden @endunless>
                <x-ui.field label="{{ __('Reason') }}" name="status_reason" required help="{{ __('e.g. Contract ended, or unpaid invoices since 2025.') }}">
                    <x-ui.textarea name="status_reason" :value="$client->status_reason" rows="2" maxlength="{{ \App\Models\Client::STATUS_REASON_MAX_LENGTH }}" required />
                </x-ui.field>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-header">
            <h2 class="card-title">{{ __('Contact persons') }}</h2>
            <p class="card-desc">{{ __('Up to :count people, the main contact first. Leave unused rows blank.', ['count' => \App\Models\Client::MAX_CONTACTS]) }}</p>
        </div>
        <div class="card-content space-y-4">
            @error('contacts')<x-ui.alert variant="danger">{{ $message }}</x-ui.alert>@enderror
            <div class="hidden gap-3 text-sm font-medium lg:grid lg:grid-cols-[2rem_minmax(0,1fr)_12rem_11rem_minmax(0,1fr)]" aria-hidden="true">
                <span></span><span>{{ __('Name') }}</span><span>{{ __('Position') }}</span><span>{{ __('Phone') }}</span><span>{{ __('Email') }}</span>
            </div>
            @for ($row = 0; $row < \App\Models\Client::MAX_CONTACTS; $row++)
                @php $contact = $contacts[$row] ?? []; @endphp
                <fieldset class="grid items-start gap-3 lg:grid-cols-[2rem_minmax(0,1fr)_12rem_11rem_minmax(0,1fr)]">
                    <legend class="sr-only">{{ __('Contact :n', ['n' => $row + 1]) }}</legend>
                    <span class="pt-2 text-sm tabular-nums text-muted-foreground" aria-hidden="true">{{ $row + 1 }}.</span>
                    @foreach (['name' => [__('Name'), 'text', 150], 'position' => [__('Position'), 'text', 100], 'phone' => [__('Phone'), 'tel', 20], 'email' => [__('Email'), 'email', 255]] as $field => [$label, $type, $max])
                        <div>
                            <label for="f-contacts-{{ $row }}-{{ $field }}" class="label lg:sr-only">{{ __('Contact :n: :field', ['n' => $row + 1, 'field' => mb_strtolower($label)]) }}</label>
                            <x-ui.input id="f-contacts-{{ $row }}-{{ $field }}" name="contacts[{{ $row }}][{{ $field }}]" :type="$type" :value="$contact[$field] ?? null" maxlength="{{ $max }}" :invalid="$errors->has('contacts.'.$row.'.'.$field)" />
                            @error('contacts.'.$row.'.'.$field)<p class="field-error">{{ $message }}</p>@enderror
                        </div>
                    @endforeach
                </fieldset>
            @endfor
        </div>
    </section>

    <div class="flex justify-end">
        <button type="submit" class="btn btn-primary">{{ $editing ? __('Save changes') : __('Add client') }}</button>
    </div>
</form>
</x-layouts.admin>
