@props(['icon' => 'file-text', 'title' => null, 'description' => null])
@php $title ??= __('No data yet'); @endphp

<div class="flex flex-col items-center justify-center gap-2 px-6 py-14 text-center">
    <span class="avatar avatar-xl avatar-soft">
        <x-ui.icon :name="$icon" class="h-6 w-6" />
    </span>
    <p class="font-medium">{{ $title }}</p>
    @if ($description)
        <p class="max-w-sm text-sm text-muted-foreground">{{ $description }}</p>
    @endif
    @if (trim($slot))
        <div class="mt-2">{{ $slot }}</div>
    @endif
</div>
