@props(['compact' => false])
{{-- Posts the chosen interface language to locale.update; the SetLocale middleware applies it. --}}
<form method="POST" action="{{ route('locale.update') }}" {{ $attributes->class($compact ? 'flex gap-1' : 'grid grid-cols-2 gap-2') }}>
    @csrf
    @foreach (config('cendana.locales') as $code => $name)
        <button type="submit" name="locale" value="{{ $code }}" lang="{{ $code }}"
            @class(['btn btn-sm' => $compact, 'btn-outline' => $compact && app()->getLocale() !== $code, 'btn-soft' => $compact && app()->getLocale() === $code, 'pref-btn' => ! $compact])
            aria-pressed="{{ app()->getLocale() === $code ? 'true' : 'false' }}">{{ $compact ? strtoupper($code) : $name }}</button>
    @endforeach
</form>
