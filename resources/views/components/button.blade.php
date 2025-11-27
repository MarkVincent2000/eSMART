@props([
    'type' => 'button',
    'variant' => 'solid', // solid, outline, soft, ghost, link
    'color' => 'primary',
    'size' => null, // sm, md, lg
    'loading' => false,
    'loadingText' => null,
    'block' => false,
    'pill' => false,
    'icon' => null,
    'iconPosition' => 'left', // left, right
    'wireTarget' => null, // Livewire loading target
])
@php
    $normalizedVariant = in_array($variant, ['solid', 'outline', 'soft', 'ghost', 'link'], true) ? $variant : 'solid';
    $validColors = ['primary', 'secondary', 'success', 'info', 'warning', 'danger', 'dark', 'light'];
    $normalizedColor = in_array($color, $validColors, true) ? $color : 'primary';

    $classes = collect(['btn']);

    $classes->push(match ($normalizedVariant) {
        'outline' => 'btn-outline-' . $normalizedColor,
        'soft' => 'btn-soft-' . $normalizedColor,
        'ghost' => 'btn-ghost-' . $normalizedColor,
        'link' => 'btn-link text-' . $normalizedColor,
        default => 'btn-' . $normalizedColor,
    });

    if (in_array($size, ['sm', 'lg'], true)) {
        $classes->push('btn-' . $size);
    }

    if ($block) {
        $classes->push('w-100');
    }

    if ($pill) {
        $classes->push('rounded-pill');
    }

    // Check if button has wire:click attribute for Livewire loading
    $hasWireClick = $attributes->whereStartsWith('wire:click')->isNotEmpty();
    $isDisabled = $attributes->get('disabled') !== null || ($loading && !$hasWireClick);

    // Determine loading state (static loading only when no wire:click)
    $showLoading = $loading && !$hasWireClick;
@endphp
    
<button
        type="{{ $type }}"
    {{ $attributes->class($classes->implode(' '))->merge([
    'disabled' => $isDisabled,
]) }}
>
{{-- Livewire Loading Spinner (shows only when wire:click is loading) --}}
@if ($hasWireClick)
    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true" 
          wire:loading @if($wireTarget) wire:target="{{ $wireTarget }}" @endif></span>
        <span wire:loading @if($wireTarget) wire:target="{{ $wireTarget }}" @endif>{{ $loadingText ?? $slot }}</span>
@endif
    {{-- Static Loading Spinner (when loading prop is true and no wire:click) --}}
@if ($showLoading)
    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
            <span>{{ $loadingText ?? $slot }}</span>
@else
        {{-- Normal Content (hidden when Livewire is loading) --}}
    <span wire:loading.remove @if($wireTarget) wire:target="{{ $wireTarget }}" @endif>
        @if ($icon && $iconPosition === 'left')
            <i class="{{ $icon }} me-2"></i>
        @endif

        {{ $slot }}

        @if ($icon && $iconPosition === 'right')
            <i class="{{ $icon }} ms-2"></i>
        @endif
    </span>
@endif
</button>

