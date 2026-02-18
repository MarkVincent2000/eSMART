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
        selectedLabels: [],
        multiple: {{ $multiple ? 'true' : 'false' }},
        disabled: {{ $disabled ? 'true' : 'false' }},
        isOpening: false,
        dropdownPosition: 'bottom', // 'top' or 'bottom'
        init() {
            if (this.selected === null) {
                this.selected = this.multiple ? [] : '';
            }
            // Ensure selected is array if multiple
            if (this.multiple && !Array.isArray(this.selected)) {
                this.selected = [];
            }
            
            // Helper function to sync selectedLabels
            const syncSelectedLabels = () => {
                if (this.multiple) {
                    this.selectedLabels = this.options.filter(opt => 
                        this.selected && Array.isArray(this.selected) && this.selected.includes(opt.value)
                    ).map(opt => opt.label || '').filter(label => label && label.trim() !== '');
                } else {
                    const found = this.options.find(opt => opt.value == this.selected);
                    this.selectedLabels = found && found.label ? [found.label] : [''];
                }
            };
            
            if (this.multiple) {
                // Watch selected values
                this.$watch('selected', value => {
                    if (!value || !Array.isArray(value)) {
                        this.selected = [];
                    }
                    syncSelectedLabels();
                });
                // Watch options changes and re-sync labels
                this.$watch('options', () => {
                    syncSelectedLabels();
                }, { deep: true });
                syncSelectedLabels();
            } else {
                // Watch selected value
                this.$watch('selected', value => {
                    syncSelectedLabels();
                });
                // Watch options changes and re-sync labels
                this.$watch('options', () => {
                    syncSelectedLabels();
                }, { deep: true });
                syncSelectedLabels();
            }
            
            // Add resize and scroll event listeners to recalculate position
            window.addEventListener('resize', () => {
                if (this.open) {
                    this.checkPosition();
                }
            });
            
            window.addEventListener('scroll', () => {
                if (this.open) {
                    this.checkPosition();
                }
            });
        },
        checkPosition() {
            if (!this.open) return;
            
            const button = this.$refs.selectButton;
            const dropdown = this.$refs.dropdown;
            
            if (!button || !dropdown) return;
            
            const buttonRect = button.getBoundingClientRect();
            const dropdownHeight = 300; // Approximate dropdown height
            const viewportHeight = window.innerHeight;
            const spaceBelow = viewportHeight - buttonRect.bottom;
            const spaceAbove = buttonRect.top;
            
            // If there's not enough space below but enough space above, position on top
            if (spaceBelow < dropdownHeight && spaceAbove > dropdownHeight) {
                this.dropdownPosition = 'top';
            } else {
                this.dropdownPosition = 'bottom';
            }
        },
        get filteredOptions() {
            if (!Array.isArray(this.options)) {
                return [];
            }
            if (this.search === '') {
                return this.options;
            }
            return this.options.filter(option => {
                if (!option || !option.label) {
                    return false;
                }
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
            if (this.disabled) return;
            if (!this.open) {
                // Opening: set flag first, then dispatch event to close others
                // The flag prevents this select from closing when it receives its own event
                this.isOpening = true;
                // Use $nextTick to ensure flag is set before dispatching event
                this.$nextTick(() => {
                    this.$dispatch('close-select-dropdowns');
                    // Open this select immediately after dispatching
                    this.open = true;
                    this.isOpening = false;
                    this.$nextTick(() => {
                        this.checkPosition();
                        if (this.$refs.searchInput) {
                            this.$refs.searchInput.focus();
                        }
                    });
                });
            } else {
                // Closing: just close
                this.open = false;
                this.search = '';
            }
        },
        toggleOption(value, label) {
            if (!this.multiple) {
                this.selected = value;
                this.selectedLabels = [label];
                this.open = false;
                this.search = '';
                return;
            }
            
            // Ensure selected is always an array
            if (!this.selected || !Array.isArray(this.selected)) {
                this.selected = [];
            }
            const index = this.selected.indexOf(value);
            
            if (index === -1) {
                this.selected.push(value);
                this.selectedLabels.push(label);
            } else {
                this.selected.splice(index, 1);
                this.selectedLabels.splice(index, 1);
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
            try {
                if (value === undefined || value === null || value === '') {
                    return false;
                }
                if (this.multiple) {
                    if (!Array.isArray(this.selected)) {
                        return false;
                    }
                    return this.selected.includes(value);
                }
                return this.selected == value;
            } catch (e) {
                return false;
            }
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
            if (!this.isOpening) {
                this.open = false;
                this.search = '';
            }
        },
        clear() {
            if (this.multiple) {
                this.selected = [];
                this.selectedLabels = [];
            } else {
                this.selected = '';
                this.selectedLabels = [''];
            }
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
            x-ref="selectButton"
            class="form-control text-start d-flex justify-content-between align-items-center select-trigger {{ $disabled ? 'disabled' : '' }}"
            :class="{'active': open, 'has-selection': selected && (multiple ? selected.length > 0 : selected !== '')}"
            style="cursor: pointer;"
            {{ $disabled ? 'disabled' : '' }}
        >
            <span class="text-truncate" 
                  :class="{'text-muted': !selected || (multiple && selected.length === 0) || (!multiple && selected === '')}"
                  x-text="displayValue"></span>

            <div class="d-flex align-items-center ms-2">
                @if(!$multiple)
                    <span
                        x-show="selected && selected !== ''"
                        @click.stop="clear()"
                        class="text-muted me-1"
                        style="cursor: pointer;"
                    >
                        <i class="ri-close-circle-line"></i>
                    </span>
                @endif
                <i class="ri-arrow-down-s-line fs-5 transition-transform" 
                   :class="{'ri-arrow-up-s-line': open, 'ri-arrow-down-s-line': !open}"
                   style="transition: transform 0.2s ease; color: #6c757d;"></i>
            </div>
        </button>

        <div 
            x-show="open && !disabled" 
            x-cloak
            x-ref="dropdown"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="card position-absolute w-100 shadow-sm z-3 select-dropdown-dark" 
            :class="dropdownPosition === 'top' ? 'mb-1' : 'mt-1'"
            :style="dropdownPosition === 'top' ? 'bottom: 100%; top: auto;' : 'top: 100%; bottom: auto;'"
            style="max-height: 300px; display: none; overflow: hidden;"
            @click.stop
        >
            <div class="card-body p-0 d-flex flex-column" style="max-height: 300px;">
                @if($searchable)
                    <div class="p-2 border-bottom">

                        <div class="search-box">
                            <input type="text" class="form-control search"
                                placeholder="Search..."
                                x-ref="searchInput"
                                x-model="search">
                            <i class="ri-search-line search-icon"></i>
                        </div>
                        
                    </div>
                @endif

                <div style="overflow-y: auto; flex: 1;">
                    <ul class="list-group list-group-flush mb-0">
                        <template x-for="(option, index) in filteredOptions" :key="'opt-' + index + '-' + (option && option.value ? String(option.value) : String(index))">
                            <li 
                                x-data="{
                                    itemIndex: index,
                                    get currentOption() {
                                        try {
                                            if (typeof filteredOptions !== 'undefined' && Array.isArray(filteredOptions) && this.itemIndex >= 0) {
                                                return filteredOptions[this.itemIndex] || null;
                                            }
                                            return null;
                                        } catch(e) {
                                            return null;
                                        }
                                    },
                                    get optValue() {
                                        try {
                                            const opt = this.currentOption;
                                            if (opt && opt.value !== undefined && opt.value !== null) {
                                                return opt.value;
                                            }
                                            return '';
                                        } catch(e) {
                                            return '';
                                        }
                                    },
                                    get optLabel() {
                                        try {
                                            const opt = this.currentOption;
                                            if (opt && opt.label !== undefined && opt.label !== null) {
                                                return opt.label;
                                            }
                                            return '';
                                        } catch(e) {
                                            return '';
                                        }
                                    },
                                    get isOptSelected() {
                                        try {
                                            const val = this.optValue;
                                            if (!val || val === '') return false;
                                            if (typeof isSelected !== 'function') return false;
                                            return isSelected(val);
                                        } catch(e) {
                                            return false;
                                        }
                                    }
                                }"
                                @click="toggleOption(optValue, optLabel)"
                                @mouseenter="$el.style.backgroundColor = isOptSelected ? '#e7f1ff' : '#f8f9fa'"
                                @mouseleave="$el.style.backgroundColor = isOptSelected ? '#e7f1ff' : 'transparent'"
                                class="list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center px-3 py-2 border-0"
                                :class="{'bg-primary-subtle text-primary fw-semibold': isOptSelected}"
                                style="cursor: pointer; transition: all 0.15s ease;"
                            >
                                <span class="d-flex align-items-center">
                                    <i x-show="isOptSelected" class="ri-checkbox-circle-fill me-2 text-primary" style="font-size: 1.1rem;"></i>
                                    <i x-show="!isOptSelected" class="ri-checkbox-blank-circle-line me-2 text-muted" style="font-size: 1.1rem; opacity: 0.5;"></i>
                                    <span x-text="optLabel || ''"></span>
                                </span>
                                <i x-show="isOptSelected" class="ri-check-line text-primary fw-bold fs-5"></i>
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
    [x-cloak] {
        display: none !important;
    }
    
    .select-trigger.has-selection:not(.disabled) {
        font-weight: 500;
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
