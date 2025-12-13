@props([
    'options' => [], // Array of options ['value' => 'Label'] or [['value' => '1', 'label' => 'One']]
    'placeholder' => 'Select an option',
    'label' => null,
    'id' => null,
    'multiple' => false,
    'searchable' => true,
    'name' => null,
    'disabled' => false,
    'value' => null, // Initial selected value(s)
])

@php
    // Generate unique ID if not provided
    $id = $id ?? 'select-' . uniqid();
    
    // Normalize options to [['value' => '...', 'label' => '...']] format
    $normalizedOptions = [];
    foreach ($options as $key => $val) {
        if (is_array($val) && isset($val['value']) && isset($val['label'])) {
            $normalizedOptions[] = $val;
        } else {
            $normalizedOptions[] = ['value' => $key, 'label' => $val];
        }
    }
    
    // Handle initial value
    $initialValue = old($name, $value);
    if ($multiple && !is_array($initialValue) && $initialValue !== null) {
        $initialValue = [$initialValue];
    }
@endphp

<div 
    class="vanilla-select-wrapper position-relative"
    data-select-id="{{ $id }}"
    {{ $attributes->except(['name', 'value']) }}
>
    @if($label)
        <label class="form-label" for="{{ $id }}">{{ $label }}</label>
    @endif

    <div class="position-relative">
        <!-- Display button -->
        <button 
            type="button" 
            id="{{ $id }}-button"
            class="form-control text-start d-flex justify-content-between align-items-center {{ $disabled ? 'disabled' : '' }}"
            style="min-height: 38px; cursor: pointer;"
            {{ $disabled ? 'disabled' : '' }}
        >
            <span class="text-truncate" id="{{ $id }}-display">{{ $placeholder }}</span>
            <i class="ri-arrow-down-s-line ms-2" id="{{ $id }}-arrow"></i>
        </button>

        <!-- Hidden input(s) for form submission -->
        @if($multiple)
            <input type="hidden" name="{{ $name }}[]" value="" id="{{ $id }}-hidden-empty">
            <div id="{{ $id }}-hidden-inputs"></div>
        @else
            <input type="hidden" name="{{ $name }}" value="{{ $initialValue ?? '' }}" id="{{ $id }}-hidden-input">
        @endif

        <!-- Dropdown menu -->
        <div 
            id="{{ $id }}-dropdown"
            class="card position-absolute w-100 shadow mt-1 z-3" 
            style="max-height: 300px; display: none;"
        >
            <div class="card-body p-0 d-flex flex-column" style="max-height: 300px;">
                @if($searchable)
                    <div class="p-2 border-bottom">
                        <input 
                            id="{{ $id }}-search"
                            type="text" 
                            class="form-control form-control-sm" 
                            placeholder="Search..."
                        >
                    </div>
                @endif

                <div style="overflow-y: auto; flex: 1;">
                    <ul class="list-group list-group-flush" id="{{ $id }}-options-list">
                        <!-- Options will be rendered here by JavaScript -->
                    </ul>
                    <div id="{{ $id }}-no-results" class="text-muted text-center p-3" style="display: none;">
                        No results found
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function() {
    const selectId = '{{ $id }}';
    const isMultiple = {{ $multiple ? 'true' : 'false' }};
    const isSearchable = {{ $searchable ? 'true' : 'false' }};
    const isDisabled = {{ $disabled ? 'true' : 'false' }};
    const placeholder = '{{ $placeholder }}';
    const allOptions = @json($normalizedOptions);
    
    // Initialize selected value(s)
    let selected = @json($initialValue);
    if (selected === null) {
        selected = isMultiple ? [] : '';
    }
    if (isMultiple && !Array.isArray(selected)) {
        selected = [];
    }

    // Get DOM elements
    const button = document.getElementById(`${selectId}-button`);
    const dropdown = document.getElementById(`${selectId}-dropdown`);
    const displayText = document.getElementById(`${selectId}-display`);
    const arrow = document.getElementById(`${selectId}-arrow`);
    const searchInput = document.getElementById(`${selectId}-search`);
    const optionsList = document.getElementById(`${selectId}-options-list`);
    const noResults = document.getElementById(`${selectId}-no-results`);
    const hiddenInput = document.getElementById(`${selectId}-hidden-input`);
    const hiddenInputsContainer = document.getElementById(`${selectId}-hidden-inputs`);

    let isOpen = false;
    let searchQuery = '';

    // Render options based on search query
    function renderOptions() {
        const filteredOptions = searchQuery === '' 
            ? allOptions 
            : allOptions.filter(option => 
                option.label.toLowerCase().includes(searchQuery.toLowerCase())
            );

        optionsList.innerHTML = '';

        if (filteredOptions.length === 0) {
            noResults.style.display = 'block';
            return;
        }

        noResults.style.display = 'none';

        filteredOptions.forEach(option => {
            const li = document.createElement('li');
            li.className = 'list-group-item list-group-item-action cursor-pointer d-flex justify-content-between align-items-center p-2 border-0 rounded';
            li.style.cursor = 'pointer';
            
            const isSelected = checkIfSelected(option.value);
            if (isSelected) {
                li.classList.add('bg-light', 'text-primary', 'fw-medium');
            }

            const span = document.createElement('span');
            span.textContent = option.label;
            li.appendChild(span);

            if (isSelected) {
                const icon = document.createElement('i');
                icon.className = 'ri-check-line text-primary';
                li.appendChild(icon);
            }

            li.addEventListener('click', function() {
                selectOption(option.value);
            });

            optionsList.appendChild(li);
        });
    }

    // Check if a value is selected
    function checkIfSelected(value) {
        if (isMultiple) {
            return selected.includes(value);
        }
        return selected == value;
    }

    // Update display text
    function updateDisplay() {
        if (isMultiple) {
            if (!selected || selected.length === 0) {
                displayText.textContent = placeholder;
                return;
            }
            
            const labels = selected.map(val => {
                const option = allOptions.find(o => o.value == val);
                return option ? option.label : val;
            });
            
            displayText.textContent = labels.join(', ');
        } else {
            if (!selected) {
                displayText.textContent = placeholder;
                return;
            }
            const option = allOptions.find(o => o.value == selected);
            displayText.textContent = option ? option.label : placeholder;
        }
    }

    // Update hidden input values
    function updateHiddenInputs() {
        if (isMultiple) {
            hiddenInputsContainer.innerHTML = '';
            if (selected && selected.length > 0) {
                selected.forEach(val => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = '{{ $name }}[]';
                    input.value = val;
                    hiddenInputsContainer.appendChild(input);
                });
            }
        } else {
            if (hiddenInput) {
                hiddenInput.value = selected || '';
            }
        }

        // Dispatch change event for form validation and other listeners
        const changeEvent = new Event('change', { bubbles: true });
        if (isMultiple) {
            hiddenInputsContainer.dispatchEvent(changeEvent);
        } else if (hiddenInput) {
            hiddenInput.dispatchEvent(changeEvent);
        }
    }

    // Toggle dropdown
    function toggle() {
        if (isDisabled) return;
        
        isOpen = !isOpen;
        
        if (isOpen) {
            dropdown.style.display = 'block';
            arrow.classList.remove('ri-arrow-down-s-line');
            arrow.classList.add('ri-arrow-up-s-line');
            button.classList.add('active');
            
            if (isSearchable && searchInput) {
                setTimeout(() => searchInput.focus(), 100);
            }
        } else {
            close();
        }
    }

    // Close dropdown
    function close() {
        isOpen = false;
        dropdown.style.display = 'none';
        arrow.classList.remove('ri-arrow-up-s-line');
        arrow.classList.add('ri-arrow-down-s-line');
        button.classList.remove('active');
        searchQuery = '';
        if (searchInput) {
            searchInput.value = '';
        }
        renderOptions();
    }

    // Select an option
    function selectOption(value) {
        if (isMultiple) {
            if (selected.includes(value)) {
                selected = selected.filter(v => v != value);
            } else {
                selected.push(value);
            }
        } else {
            selected = value;
            close();
        }
        
        updateDisplay();
        updateHiddenInputs();
        renderOptions();
    }

    // Event listeners
    button.addEventListener('click', toggle);

    // Search functionality
    if (isSearchable && searchInput) {
        searchInput.addEventListener('input', function(e) {
            searchQuery = e.target.value;
            renderOptions();
        });
    }

    // Close on click outside
    document.addEventListener('click', function(e) {
        const wrapper = document.querySelector(`[data-select-id="${selectId}"]`);
        if (wrapper && !wrapper.contains(e.target)) {
            close();
        }
    });

    // Close on escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape' && isOpen) {
            close();
        }
    });

    // Initial render
    renderOptions();
    updateDisplay();
    updateHiddenInputs();

    // Expose methods for external access if needed
    window[`vanillaSelect_${selectId}`] = {
        getValue: () => selected,
        setValue: (value) => {
            selected = value;
            if (isMultiple && !Array.isArray(selected)) {
                selected = [];
            }
            updateDisplay();
            updateHiddenInputs();
            renderOptions();
        },
        setOptions: (newOptions) => {
            // Clear current options and selection
            allOptions.length = 0;
            
            // Normalize and add new options
            newOptions.forEach(option => {
                if (typeof option === 'object' && option.value !== undefined && option.label !== undefined) {
                    allOptions.push(option);
                } else if (typeof option === 'object') {
                    // Handle {key: value} format
                    Object.keys(option).forEach(key => {
                        allOptions.push({ value: key, label: option[key] });
                    });
                }
            });
            
            renderOptions();
            updateDisplay();
        },
        addOption: (value, label) => {
            allOptions.push({ value, label });
            renderOptions();
        },
        clearOptions: () => {
            allOptions.length = 0;
            selected = isMultiple ? [] : '';
            renderOptions();
            updateDisplay();
            updateHiddenInputs();
        },
        enable: () => {
            if (button) {
                button.disabled = false;
                button.classList.remove('disabled');
            }
        },
        disable: () => {
            if (button) {
                button.disabled = true;
                button.classList.add('disabled');
            }
            close();
        },
        close: close,
        open: toggle
    };
})();
</script>
