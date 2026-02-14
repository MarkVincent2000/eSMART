@props([
    'id' => null,
    'show' => false,
    'size' => 'md', // sm, md, lg, xl, fullscreen
    'centered' => false,
    'scrollable' => false,
    'contentScrollable' => true, // Enable/disable scrollbar in modal body content
    'keyboard' => true,
    'title' => null,
    'showHeader' => true,
    'showFooter' => false,
    'showCloseButton' => true,
    'closeOnBackdrop' => false,
    'maxWidth' => null,
    'zIndex' => 1055,
    'overflow' => 'hidden',
    'verticalAlign' => 'center', // center or top
])

@php
    // Generate unique ID if not provided, use wire model if available
    $modalId = $id ?? ($attributes->wire('model') ? md5($attributes->wire('model')) : 'modal-' . uniqid());

    // Validate size
    $validSizes = ['sm', 'md', 'lg', 'xl', 'fullscreen'];
    $normalizedSize = in_array($size, $validSizes, true) ? $size : 'md';

    // Build modal dialog classes
    $dialogClasses = collect(['modal-dialog']);

    // Add size class
    if ($normalizedSize !== 'md') {
        $dialogClasses->push('modal-' . $normalizedSize);
    }

    // Add centered class
    if ($centered) {
        $dialogClasses->push('modal-dialog-centered');
    }

    // Add scrollable class
    if ($scrollable) {
        $dialogClasses->push('modal-dialog-scrollable');
    }

    // Validate vertical alignment
    $validAlignments = ['center', 'top'];
    $verticalAlign = in_array($verticalAlign, $validAlignments, true) ? $verticalAlign : 'center';

    // Check if wire:model is provided via attributes
    $hasWireModel = $attributes->whereStartsWith('wire:model')->isNotEmpty();
@endphp

<div
    x-data="{
        @if($hasWireModel)
            show: @entangle($attributes->wire('model')),
        @else
            show: @js($show),
        @endif
        closeOnBackdrop: @js($closeOnBackdrop),
        init() {
            this.$watch('show', (value) => {
                if (value) {
                    this.$nextTick(() => {
                        // Focus the modal card for accessibility
                        const modalCard = this.$el.querySelector('.modal-card-dark');
                        if (modalCard) {
                            modalCard.focus();
                        }
                    });
                }
            });
        }
    }"
    x-on:close.stop="show = false"
    x-on:keydown.escape.window="show && closeOnBackdrop && (show = false)"
    x-show="show"
    x-cloak
    id="{{ $modalId }}"
    class="custom-modal-overlay"
    style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; z-index: 9999;"
    tabindex="-1"
    role="dialog"
    :aria-modal="show ? 'true' : null"
>
    <!-- Modal Backdrop -->
    <div
        x-show="show"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        style="position: fixed; top: 0; left: 0; right: 0; bottom: 0; background-color: rgba(0, 0, 0, 0.5); cursor: pointer; pointer-events: auto;"
        x-on:click="closeOnBackdrop && (show = false)"
    ></div>

    <!-- Modal Dialog Container -->
    <div style="position: relative; z-index: 1;" class="d-flex {{ $verticalAlign === 'top' ? 'align-items-start pt-5' : '' }} min-vh-100 p-3 overflow-y-auto pointer-events-none">
        <div
            x-show="show"
            x-trap.noscroll="show"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 transform -translate-y-12 scale-95"
            x-transition:enter-end="opacity-100 transform translate-y-0 scale-100"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 transform translate-y-0 scale-100"
            x-transition:leave-end="opacity-0 transform -translate-y-12 scale-95"
            class="w-full {{ $normalizedSize === 'sm' ? 'sm:max-w-sm' : ($normalizedSize === 'lg' ? 'sm:max-w-lg' : ($normalizedSize === 'xl' ? 'sm:max-w-xl' : ($normalizedSize === 'fullscreen' ? 'max-w-full h-full' : 'sm:max-w-md'))) }} m-auto pointer-events-auto"
            style="{{ $maxWidth ? 'max-width: ' . $maxWidth . ';' : '' }}"
        >
        <div 
            class="card mb-0 shadow-lg modal-card-dark" 
            style="border-radius: 0.5rem; user-select: text; -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; max-height: calc(100dvh - 2rem); display: flex; flex-direction: column; overflow: {{ $overflow }}; outline: none;"
            tabindex="-1"
            x-on:click.stop
        >
                    <!-- Modal Header -->
                @if($showHeader)
                    <div class="card-header border-bottom d-flex align-items-center justify-content-between" style="user-select: text; -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; flex-shrink: 0;">
                        @if(isset($header))
                            {{ $header }}
                        @else
                            @if($title)
                                <h5 class="modal-title mb-0">{{ $title }}</h5>
                            @endif

                            @if($showCloseButton)
                                <button
                                    type="button"
                                    class="btn-close"
                                    x-on:click="show = false"
                                    aria-label="Close"
                                ></button>
                            @endif
                        @endif
                    </div>
                @endif

                <!-- Modal Body -->
                <div 
                    class="card-body" 
                    style="user-select: text; -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; {{ $contentScrollable ? 'overflow-y: auto !important;' : 'overflow-y: visible !important;' }} {{ $overflow === 'visible' ? 'overflow-x: visible !important;' : 'overflow-x: hidden !important;' }} flex: 1 1 auto; min-height: 0;"
                >
                    {{ $slot }}
                </div>

                <!-- Modal Footer -->
                @if($showFooter || isset($footer))
                    <div class="card-footer border-top d-flex justify-content-end gap-2" style="user-select: text; -webkit-user-select: text; -moz-user-select: text; -ms-user-select: text; flex-shrink: 0;">
                        @if(isset($footer))
                            {{ $footer }}
                        @else
                            <button type="button" class="btn btn-light" x-on:click="show = false">Close</button>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>


