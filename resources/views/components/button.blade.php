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
    'iconOnly' => false, // If true, adds btn-icon class for icon-only buttons
    'tooltip' => null, // Tooltip text (enables Bootstrap tooltip)
    'tooltipPlacement' => 'top', // top, bottom, left, right
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

    // Add btn-icon class if iconOnly is true
    if ($iconOnly) {
        $classes->push('btn-icon');
    }

    // Check if button has wire:click attribute for Livewire loading
    $hasWireClick = $attributes->whereStartsWith('wire:click')->isNotEmpty();
    $isDisabled = $attributes->get('disabled') !== null || ($loading && !$hasWireClick);

    // Determine loading state (static loading only when no wire:click)
    $showLoading = $loading && !$hasWireClick;
    
    // Determine icon margin class based on iconOnly
    $iconMarginClass = $iconOnly ? '' : ($iconPosition === 'left' ? 'me-2' : 'ms-2');
    
    // Determine spinner margin class - no margin when iconOnly to center it
    $spinnerMarginClass = $iconOnly ? '' : 'me-2';
    
    // Build tooltip attributes if tooltip is provided
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
{{-- Livewire Loading Spinner (shows only when wire:click is loading) --}}
@if ($hasWireClick)
    <span class="spinner-border spinner-border-sm{{ $spinnerMarginClass ? ' ' . $spinnerMarginClass : '' }}" role="status" aria-hidden="true" 
          wire:loading @if($wireTarget) wire:target="{{ $wireTarget }}" @endif></span>
    @if (!$iconOnly)
        <span wire:loading @if($wireTarget) wire:target="{{ $wireTarget }}" @endif>{{ $loadingText ?? $slot }}</span>
    @endif
@endif
    {{-- Static Loading Spinner (when loading prop is true and no wire:click) --}}
@if ($showLoading)
    <span class="spinner-border spinner-border-sm{{ $spinnerMarginClass ? ' ' . $spinnerMarginClass : '' }}" role="status" aria-hidden="true"></span>
    @if (!$iconOnly)
        <span>{{ $loadingText ?? $slot }}</span>
    @endif
@else
            {{-- Normal Content (hidden when Livewire is loading) --}}
        <span wire:loading.remove @if($wireTarget) wire:target="{{ $wireTarget }}" @endif>
            @if ($icon && $iconPosition === 'left')
                <i class="{{ $icon }}{{ $iconMarginClass ? ' ' . $iconMarginClass : '' }}"></i>
            @endif

            {{ $slot }}

            @if ($icon && $iconPosition === 'right')
                <i class="{{ $icon }}{{ $iconMarginClass ? ' ' . $iconMarginClass : '' }}"></i>
            @endif
        </span>
    @endif
</button>

@if ($tooltip)
@once
@push('scripts')
<script>
    (function() {
        // Initialize Bootstrap tooltips for dynamically added elements (Livewire updates)
        function initTooltips() {
            if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
                // Destroy existing tooltips first to avoid duplicates
                const allTooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                allTooltipElements.forEach(function(element) {
                    const existingTooltip = bootstrap.Tooltip.getInstance(element);
                    if (existingTooltip) {
                        existingTooltip.dispose();
                    }
                });
                
                // Find all tooltip elements (including new ones from Livewire updates)
                const tooltipElements = document.querySelectorAll('[data-bs-toggle="tooltip"]');
                tooltipElements.forEach(function(element) {
                    // Only initialize if not already initialized
                    if (!bootstrap.Tooltip.getInstance(element)) {
                        new bootstrap.Tooltip(element);
                    }
                });
            }
        }
        
        // Initialize tooltips on page load
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initTooltips);
        } else {
            initTooltips();
        }
        
        // Initialize tooltips after Livewire loads/updates
        document.addEventListener('livewire:load', initTooltips);
        document.addEventListener('livewire:initialized', initTooltips);
        document.addEventListener('livewire:update', initTooltips);
        
        // Also listen for Livewire's morphing completion
        Livewire.hook('morph.updated', ({ el, component }) => {
            setTimeout(initTooltips, 10);
        });
    })();
</script>
@endpush
@endonce
@endif

