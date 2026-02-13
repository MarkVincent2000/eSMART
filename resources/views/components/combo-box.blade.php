@props([
    'options' => [], // Array of options [['value' => '1', 'label' => 'One'], ...]
    'placeholder' => 'Select an option',
    'label' => null,
    'id' => null,
    'multiple' => false,
    'searchable' => true,
    'disabled' => false,
    'required' => false,
])

@php
    $id = $id ?? 'combo-' . md5($attributes->wire('model'));
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
    wire:ignore
    x-data="{
        open: false,
        search: '',
        selectedValue: @entangle($attributes->wire('model')),
        options: @js($normalizedOptions),
        multiple: {{ $multiple ? 'true' : 'false' }},
        disabled: {{ $disabled ? 'true' : 'false' }},
        
        get filteredOptions() {
            if (!this.search) return this.options;
            return this.options.filter(opt => 
                opt.label.toLowerCase().includes(this.search.toLowerCase())
            );
        },
        
        get displayText() {
            if (this.multiple) {
                if (!this.selectedValue || this.selectedValue.length === 0) {
                    return '{{ $placeholder }}';
                }
                const labels = this.selectedValue.map(val => {
                    const opt = this.options.find(o => o.value == val);
                    return opt ? opt.label : val;
                });
                return labels.join(', ');
            } else {
                if (!this.selectedValue) return '{{ $placeholder }}';
                const opt = this.options.find(o => o.value == this.selectedValue);
                return opt ? opt.label : '{{ $placeholder }}';
            }
        },
        
        isSelected(value) {
            if (this.multiple) {
                return Array.isArray(this.selectedValue) && this.selectedValue.includes(value);
            }
            return this.selectedValue == value;
        },
        
        selectOption(value) {
            if (this.disabled) return;
            
            if (this.multiple) {
                if (!Array.isArray(this.selectedValue)) {
                    this.selectedValue = [];
                }
                const index = this.selectedValue.indexOf(value);
                if (index === -1) {
                    this.selectedValue.push(value);
                } else {
                    this.selectedValue.splice(index, 1);
                }
            } else {
                this.selectedValue = value;
                this.open = false;
                this.search = '';
            }
        },
        
        toggleDropdown() {
            if (this.disabled) return;
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
        
        clearSelection() {
            if (this.disabled) return;
            this.selectedValue = this.multiple ? [] : '';
            this.search = '';
        },
        
        init() {
            // Initialize selected value
            if (this.selectedValue === null || this.selectedValue === undefined) {
                this.selectedValue = this.multiple ? [] : '';
            }
            
            // Ensure selected is array if multiple
            if (this.multiple && !Array.isArray(this.selectedValue)) {
                this.selectedValue = [];
            }
            
            // Watch for options updates from Livewire
            this.$watch('options', () => {
                // Re-validate selected value when options change
                if (this.multiple) {
                    const validValues = this.options.map(o => o.value);
                    this.selectedValue = this.selectedValue.filter(v => validValues.includes(v));
                } else {
                    const isValid = this.options.some(o => o.value == this.selectedValue);
                    if (!isValid && this.selectedValue) {
                        this.selectedValue = '';
                    }
                }
            });
        }
    }"
    @click.away="open = false"
    @close-combos.window="if (!open) return; else open = false"
    @keydown.escape.window="open = false"
    class="position-relative"
    {{ $attributes->except(['wire:model', 'wire:model.live']) }}
>
    @if($label)
        <label class="form-label fw-semibold mb-2" for="{{ $id }}" style="color: #495057; font-size: 0.875rem;">
            {{ $label }}
            @if($required)
                <span class="text-danger">*</span>
            @endif
        </label>
    @endif

    <div class="position-relative">
        <button 
            type="button" 
            @click="toggleDropdown()"
            x-ref="selectButton"
            :disabled="disabled"
            class="form-control text-start d-flex justify-content-between align-items-center"
            :class="{
                'active': open, 
                'has-selection': selectedValue && (multiple ? selectedValue.length > 0 : selectedValue !== ''),
                'disabled': disabled
            }"
            style="cursor: pointer; min-height: 38px;"
        >
            <span class="text-truncate flex-grow-1" 
                  :class="{'text-muted': !selectedValue || (multiple && selectedValue.length === 0) || (!multiple && selectedValue === '')}"
                  x-text="displayText"></span>

            <div class="d-flex align-items-center gap-1 ms-2">
                <span
                    x-show="!disabled && selectedValue && (multiple ? selectedValue.length > 0 : selectedValue !== '')"
                    @click.stop="clearSelection()"
                    class="text-muted"
                    style="cursor: pointer;"
                >
                    <i class="ri-close-circle-line"></i>
                </span>
                <i class="ri-arrow-down-s-line fs-5" 
                   :class="{'ri-arrow-up-s-line': open, 'ri-arrow-down-s-line': !open}"
                   style="transition: transform 0.2s ease; color: #6c757d;"></i>
            </div>
        </button>

        <div 
            x-show="open" 
            x-cloak
            x-ref="dropdown"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 transform scale-95"
            x-transition:enter-end="opacity-100 transform scale-100"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 transform scale-100"
            x-transition:leave-end="opacity-0 transform scale-95"
            class="card position-absolute w-100 shadow-lg mt-1" 
            style="max-height: 320px; display: none; overflow: hidden; z-index: 1050;"
            @click.stop
        >
            <div class="card-body p-0 d-flex flex-column" style="max-height: 320px;">
                @if($searchable)
                    <div class="p-2 border-bottom bg-light sticky-top">
                        <div class="position-relative">
                            <i class="ri-search-line position-absolute top-50 start-0 translate-middle-y ms-2 text-muted" style="z-index: 1;"></i>
                            <input 
                                x-ref="searchInput"
                                x-model="search" 
                                type="text" 
                                class="form-control form-control-sm ps-5" 
                                placeholder="Search..."
                                @keydown.escape="open = false"
                                style="border: 1px solid #ced4da; border-radius: 0.375rem;"
                            >
                        </div>
                    </div>
                @endif

                <div style="overflow-y: auto; flex: 1;">
                    <ul class="list-group list-group-flush mb-0">
                        <template x-for="(option, idx) in filteredOptions" :key="'opt-' + idx">
                            <li 
                                @click="selectOption(option.value)"
                                @mouseenter="$el.style.backgroundColor = isSelected(option.value) ? '#e7f1ff' : '#f8f9fa'"
                                @mouseleave="$el.style.backgroundColor = isSelected(option.value) ? '#e7f1ff' : 'transparent'"
                                class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-3 py-2 border-0"
                                :class="{'bg-primary-subtle text-primary fw-semibold': isSelected(option.value)}"
                                style="cursor: pointer; transition: all 0.15s ease;"
                            >
                                <span class="d-flex align-items-center">
                                    <i x-show="isSelected(option.value)" class="ri-checkbox-circle-fill me-2 text-primary" style="font-size: 1.1rem;"></i>
                                    <i x-show="!isSelected(option.value)" class="ri-checkbox-blank-circle-line me-2 text-muted" style="font-size: 1.1rem; opacity: 0.5;"></i>
                                    <span x-text="option.label"></span>
                                </span>
                                <i x-show="isSelected(option.value)" class="ri-check-line text-primary fw-bold fs-5"></i>
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
    
    .form-control.has-selection:not(.disabled) {
        font-weight: 500;
    }

    .form-control.disabled {
        background-color: #e9ecef;
        cursor: not-allowed;
        opacity: 0.6;
    }
</style>
