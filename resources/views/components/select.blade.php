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
        close() {
            this.open = false;
            this.search = '';
        }
    }"
    @click.away="close()"
    class="position-relative"
    {{ $attributes->except(['wire:model', 'wire:model.live']) }}
>
    @if($label)
        <label class="form-label" for="{{ $id }}">{{ $label }}</label>
    @endif

    <div class="position-relative">
        <button 
            type="button" 
            @click="toggle()"
            class="form-control text-start d-flex justify-content-between align-items-center {{ $disabled ? 'disabled' : '' }}"
            :class="{'active': open}"
            style="min-height: 38px; cursor: pointer;"
            {{ $disabled ? 'disabled' : '' }}
        >
            <span class="text-truncate" x-text="displayValue"></span>
            <i class="ri-arrow-down-s-line ms-2" :class="{'ri-arrow-up-s-line': open, 'ri-arrow-down-s-line': !open}"></i>
        </button>

        <div 
            x-show="open" 
            x-transition
            class="card position-absolute w-100 shadow mt-1 z-3" 
            style="max-height: 300px; overflow-y: auto; display: none;"
        >
            <div class="card-body p-2">
                @if($searchable)
                    <div class="mb-2">
                        <input 
                            x-ref="searchInput"
                            x-model="search" 
                            type="text" 
                            class="form-control form-control-sm" 
                            placeholder="Search..."
                        >
                    </div>
                @endif

                <ul class="list-group list-group-flush">
                    <template x-for="option in filteredOptions" :key="option.value">
                        <li 
                            @click="select(option.value)"
                            class="list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center p-2 border-0 rounded"
                            :class="{'bg-light text-primary fw-medium': isSelected(option.value)}"
                            style="cursor: pointer;"
                        >
                            <span x-text="option.label"></span>
                            <i x-show="isSelected(option.value)" class="ri-check-line text-primary"></i>
                        </li>
                    </template>
                    <li x-show="filteredOptions.length === 0" class="list-group-item text-muted text-center p-2">
                        No results found
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>

