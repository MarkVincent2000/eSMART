@props([
    'type' => 'button',
    'variant' => 'solid',
    'color' => 'primary',
    'size' => null,
    'loading' => false,
    'loadingText' => null,
    'block' => false,
    'pill' => false,
    'icon' => null,
    'iconPosition' => 'left',
    'wireTarget' => null,
    'iconOnly' => false,
    'tooltip' => null,
    'tooltipPlacement' => 'top',
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

    if ($iconOnly) {
        $classes->push('btn-icon');
    }

    $hasWireClick = $attributes->whereStartsWith('wire:click')->isNotEmpty();
    $isDisabled = $attributes->get('disabled') !== null || ($loading && !$hasWireClick);
    $showLoading = $loading && !$hasWireClick;
    
    $iconMarginClass = $iconOnly ? '' : ($iconPosition === 'left' ? 'me-2' : 'ms-2');
    $spinnerMarginClass = $iconOnly ? '' : 'me-2';
    
    $tooltipAttributes = [];
    if ($tooltip) {
        $tooltipAttributes['data-bs-toggle'] = 'tooltip';
        $tooltipAttributes['data-bs-placement'] = $tooltipPlacement;
        $tooltipAttributes['title'] = $tooltip;
    }
@endphp
    
<button
    type="{{ $type }}"
    {{ $attributes->class($classes->implode(' '))->merge(array_merge([
        'disabled' => $isDisabled,
    ], $tooltipAttributes)) }}
>
    @if ($hasWireClick)
        {{-- Livewire Loading Spinner --}}
        <span 
            class="spinner-border spinner-border-sm{{ $spinnerMarginClass ? ' ' . $spinnerMarginClass : '' }}" 
            role="status" 
            aria-hidden="true" 
            wire:loading 
            @if($wireTarget) wire:target="{{ $wireTarget }}" @endif
        ></span>
        
        @if (!$iconOnly)
            {{-- Loading Text (shown when loading, uses loadingText if provided, otherwise slot) --}}
            <span wire:loading @if($wireTarget) wire:target="{{ $wireTarget }}" @endif>
                {{ $loadingText ?? $slot }}
            </span>
            
            {{-- Normal Content (hidden when loading) --}}
            <span wire:loading.remove @if($wireTarget) wire:target="{{ $wireTarget }}" @endif>
                @if ($icon && $iconPosition === 'left')
                    <i class="{{ $icon }}{{ $iconMarginClass ? ' ' . $iconMarginClass : '' }}"></i>
                @endif

                {{ $slot }}

                @if ($icon && $iconPosition === 'right')
                    <i class="{{ $icon }}{{ $iconMarginClass ? ' ' . $iconMarginClass : '' }}"></i>
                @endif
            </span>
        @else
            {{-- Icon only buttons: just show/hide icon --}}
            <span wire:loading.remove @if($wireTarget) wire:target="{{ $wireTarget }}" @endif>
                @if ($icon)
                    <i class="{{ $icon }}"></i>
                @endif
            </span>
        @endif
    @elseif ($showLoading)
        {{-- Static Loading State --}}
        <span class="spinner-border spinner-border-sm{{ $spinnerMarginClass ? ' ' . $spinnerMarginClass : '' }}" role="status" aria-hidden="true"></span>
        @if (!$iconOnly)
            <span>{{ $loadingText ?? $slot }}</span>
        @endif
    @else
        {{-- Normal Content --}}
        @if ($icon && $iconPosition === 'left')
            <i class="{{ $icon }}{{ $iconMarginClass ? ' ' . $iconMarginClass : '' }}"></i>
        @endif

        {{ $slot }}

        @if ($icon && $iconPosition === 'right')
            <i class="{{ $icon }}{{ $iconMarginClass ? ' ' . $iconMarginClass : '' }}"></i>
        @endif
    @endif
</button>

@if ($tooltip)
@once
@push('scripts')
<script>
    (function () {
        function initTooltips(root = document) {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;

            const tooltipElements = root.querySelectorAll('[data-bs-toggle="tooltip"]');

            tooltipElements.forEach(el => {
                if (!el.isConnected) return;
                if (!el.hasAttribute('title')) return;

                if (!bootstrap.Tooltip.getInstance(el)) {
                    new bootstrap.Tooltip(el, {
                        trigger: 'hover focus',
                        boundary: 'viewport',
                    });
                }
            });
        }

        function destroyTooltips(root = document) {
            if (typeof bootstrap === 'undefined' || !bootstrap.Tooltip) return;

            const tooltipElements = root.querySelectorAll('[data-bs-toggle="tooltip"]');

            tooltipElements.forEach(el => {
                const instance = bootstrap.Tooltip.getInstance(el);
                if (instance) {
                    instance.dispose();
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => initTooltips());

        document.addEventListener('livewire:init', () => {
            Livewire.hook('morph.before', ({ el }) => {
                destroyTooltips(el);
            });

            Livewire.hook('morph.after', ({ el }) => {
                initTooltips(el);
            });
        });
    })();
</script>
@endpush
@endonce
@endif
