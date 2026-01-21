@props([
    'options' => [], // Array of options ['value' => 'Label'] or [['value' => '1', 'label' => 'One']]
    'placeholder' => 'Select an option',
    'label' => null,
    'id' => null,
    'multiple' => false,
    'searchable' => true,
    'name' => null,
    'disabled' => false,
    'wireModel' => null, // Internal use mostly, but can be passed
])

@php
    $id = $id ?? md5($attributes->wire('model'));
    // Normalize options to [['value' => '...', 'label' => '...']] format
    $normalizedOptions = [];
    foreach ($options as $key => $value) {
        if (is_array($value) && isset($value['value']) && isset($value['label'])) {
            $normalizedOptions[] = $value;
        } else {
            $normalizedOptions[] = ['value' => $key, 'label' => $value];
        }
    }
@endphp

<div 
    x-data="{
        open: false,
        search: '',
        selected: @entangle($attributes->wire('model')),
        options: {{ json_encode($normalizedOptions) }},
        multiple: {{ $multiple ? 'true' : 'false' }},
        init() {
            if (this.selected === null) {
                this.selected = this.multiple ? [] : '';
            }
            // Ensure selected is array if multiple
            if (this.multiple && !Array.isArray(this.selected)) {
                this.selected = [];
            }
        },
        get filteredOptions() {
            if (this.search === '') {
                return this.options;
            }
            return this.options.filter(option => {
                return option.label.toLowerCase().includes(this.search.toLowerCase());
            });
        },
        get displayValue() {
            if (this.multiple) {
                if (!this.selected || this.selected.length === 0) return '{{ $placeholder }}';
                
                // Find labels for selected values
                let labels = this.selected.map(val => {
                    let option = this.options.find(o => o.value == val); // loose comparison for string/int
                    return option ? option.label : val;
                });
                
                return labels.join(', ');
            } else {
                if (!this.selected) return '{{ $placeholder }}';
                let option = this.options.find(o => o.value == this.selected);
                return option ? option.label : '{{ $placeholder }}';
            }
        },
        toggle() {
            if ({{ $disabled ? 'true' : 'false' }}) return;
            this.open = !this.open;
            if (this.open) {
                this.$nextTick(() => {
                    if (this.$refs.searchInput) {
                        this.$refs.searchInput.focus();
                    }
                });
            } else {
                this.search = '';
            }
        },
        select(value) {
            if (this.multiple) {
                if (this.selected.includes(value)) {
                    this.selected = this.selected.filter(v => v != value);
                } else {
                    this.selected.push(value);
                }
            } else {
                this.selected = value;
                this.open = false;
                this.search = '';
            }
        },
        isSelected(value) {
            if (this.multiple) {
                return this.selected.includes(value);
            }
            return this.selected == value;
        },
        isInsideModal() {
            // Check if this select is inside a modal
            let element = this.$el;
            while (element && element !== document.body) {
                if (element.classList && element.classList.contains('custom-modal-overlay')) {
                    return true;
                }
                if (element.classList && element.classList.contains('modal-card-dark')) {
                    return true;
                }
                element = element.parentElement;
            }
            return false;
        },
        isClickOnModalBackdrop(event) {
            // Check if the click is on the modal backdrop (not inside modal-card-dark)
            // Returns true if: clicking on backdrop OR clicking outside modal entirely
            // Returns false if: clicking inside modal content (modal-card-dark)
            let target = event.target;
            let foundModalCard = false;
            let foundModalOverlay = false;
            
            // Walk up the DOM tree to find modal elements
            while (target && target !== document.body) {
                // Check for modal card (content area) - if we find this, we're inside modal content
                if (target.classList && target.classList.contains('modal-card-dark')) {
                    foundModalCard = true;
                    // Once we find the modal card, we know we're inside modal content
                    // So this is NOT a backdrop click - don't close
                    return false;
                }
                
                // Check for modal overlay container
                if (target.classList && target.classList.contains('custom-modal-overlay')) {
                    foundModalOverlay = true;
                    // If we clicked directly on the overlay itself (not a child), it's backdrop
                    if (target === event.target) {
                        return true; // Clicked directly on backdrop - close
                    }
                    // If we reached overlay without finding modal-card, we're on backdrop
                    // Continue to check if we find modal-card further up
                    target = target.parentElement;
                    continue;
                }
                
                target = target.parentElement;
            }
            
            // If we found overlay but not modal card, it's a backdrop click - close
            // If we didn't find overlay at all, it's outside modal entirely - also close
            return !foundModalCard;
        },
        handleClickAway(event) {
            // If multiple is true and inside modal, only close if clicking on modal backdrop
            if (this.multiple && this.isInsideModal()) {
                // Check if click is on modal backdrop
                const isOnBackdrop = this.isClickOnModalBackdrop(event);
                
                // Only close if clicking on backdrop (or outside modal entirely)
                if (isOnBackdrop) {
                    this.close();
                }
                // Otherwise, don't close - allow user to interact with other modal elements
            } else {
                // Normal behavior for single select or select outside modal
                this.close();
            }
        },
        close() {
            this.open = false;
            this.search = '';
        }
    }"
    @click.away="handleClickAway($event)"
    @close-select-dropdowns.window="close()"
    class="position-relative"
    {{ $attributes->except(['wire:model', 'wire:model.live']) }}
>
    @if($label)
        <label class="form-label fw-semibold mb-2" for="{{ $id }}" style="color: #495057; font-size: 0.875rem;">
            {{ $label }}
        </label>
    @endif

    <div class="position-relative">
        <button 
            type="button" 
            @click.stop="toggle()"
            class="form-control text-start d-flex justify-content-between align-items-center select-trigger {{ $disabled ? 'disabled' : '' }}"
            :class="{'active': open, 'has-selection': selected && (multiple ? selected.length > 0 : selected !== '')}"
            style="min-height: 42px; cursor: pointer; border: 2px solid #e0e0e0; transition: all 0.2s ease; background: #fff;"
            :style="open ? 'border-color: #0d6efd; box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.15);' : ''"
            {{ $disabled ? 'disabled' : '' }}
        >
            <span class="text-truncate fw-medium" 
                  :class="{'text-muted': !selected || (multiple && selected.length === 0) || (!multiple && selected === '')}"
                  x-text="displayValue"></span>
            <i class="ri-arrow-down-s-line ms-2 fs-5 transition-transform" 
               :class="{'ri-arrow-up-s-line': open, 'ri-arrow-down-s-line': !open}"
               style="transition: transform 0.2s ease; color: #6c757d;"
               :style="open ? 'transform: rotate(180deg); color: #0d6efd;' : ''"></i>
        </button>

        <div 
            x-show="open" 
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="card position-absolute w-100 shadow-lg mt-1 z-3 select-dropdown-dark" 
            style="max-height: 300px; display: none; border: 1px solid #dee2e6; border-radius: 0.5rem; overflow: hidden;"
            @click.stop
        >
            <div class="card-body p-0 d-flex flex-column" style="max-height: 300px;">
                @if($searchable)
                    <div class="p-2 border-bottom bg-light">
                        <div class="position-relative">
                            <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="z-index: 1;"></i>
                            <input 
                                x-ref="searchInput"
                                x-model="search" 
                                type="text" 
                                class="form-control form-control-sm ps-5" 
                                placeholder="Search..."
                                style="border: 1px solid #ced4da; border-radius: 0.375rem;"
                            >
                        </div>
                    </div>
                @endif

                <div style="overflow-y: auto; flex: 1;">
                    <ul class="list-group list-group-flush mb-0">
                        <template x-for="option in filteredOptions" :key="option.value">
                            <li 
                                @click="select(option.value)"
                                @mouseenter="$el.style.backgroundColor = (multiple ? selected.includes(option.value) : selected == option.value) ? '#e7f1ff' : '#f8f9fa'"
                                @mouseleave="$el.style.backgroundColor = (multiple ? selected.includes(option.value) : selected == option.value) ? '#e7f1ff' : 'transparent'"
                                class="list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center px-3 py-2 border-0"
                                :class="{'bg-primary-subtle text-primary fw-semibold': (multiple ? selected.includes(option.value) : selected == option.value)}"
                                style="cursor: pointer; transition: all 0.15s ease;"
                            >
                                <span class="d-flex align-items-center">
                                    <i x-show="multiple ? selected.includes(option.value) : selected == option.value" class="ri-checkbox-circle-fill me-2 text-primary" style="font-size: 1.1rem;"></i>
                                    <i x-show="!(multiple ? selected.includes(option.value) : selected == option.value)" class="ri-checkbox-blank-circle-line me-2 text-muted" style="font-size: 1.1rem; opacity: 0.5;"></i>
                                    <span x-text="option.label"></span>
                                </span>
                                <i x-show="multiple ? selected.includes(option.value) : selected == option.value" class="ri-check-line text-primary fw-bold fs-5"></i>
                            </li>
                        </template>
                        <li x-show="filteredOptions.length === 0" class="list-group-item text-muted text-center p-4">
                            <i class="ri-search-line fs-3 d-block mb-2 opacity-50"></i>
                            <span>No results found</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .select-trigger:hover:not(.disabled) {
        border-color: #0d6efd !important;
        box-shadow: 0 0 0 0.1rem rgba(13, 110, 253, 0.1) !important;
    }
    
    .select-trigger.has-selection:not(.disabled) {
        border-color: #0d6efd;
        background: linear-gradient(to right, #ffffff 0%, #f8f9ff 100%);
    }
    
    .select-trigger.disabled {
        background-color: #e9ecef;
        cursor: not-allowed;
        opacity: 0.6;
    }
    
    .select-dropdown-dark {
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
    }
    
    .select-dropdown-dark .list-group-item:hover {
        background-color: #f8f9fa !important;
    }
    
    .select-dropdown-dark .list-group-item.bg-primary-subtle:hover {
        background-color: #e7f1ff !important;
    }
</style>

