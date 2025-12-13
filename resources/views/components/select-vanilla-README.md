# Vanilla JavaScript Select Component

A custom select/dropdown component built with vanilla JavaScript (no Livewire, no Alpine.js). This component provides a searchable, styleable dropdown interface with single and multiple selection support.

## Features

-   ✅ **Pure Vanilla JavaScript** - No framework dependencies
-   ✅ **Single & Multiple Selection** - Support for both modes
-   ✅ **Searchable** - Optional search/filter functionality
-   ✅ **Form Compatible** - Works with standard HTML forms
-   ✅ **Pre-selected Values** - Support for default values
-   ✅ **Programmatic Control** - JavaScript API for external control
-   ✅ **Bootstrap Styled** - Uses Bootstrap classes
-   ✅ **Keyboard Support** - ESC key to close dropdown
-   ✅ **Click Outside** - Closes when clicking outside
-   ✅ **Disabled State** - Can be disabled like regular inputs

## Basic Usage

### Single Select

```blade
<x-select-vanilla
    :options="[
        '1' => 'Option 1',
        '2' => 'Option 2',
        '3' => 'Option 3',
    ]"
    name="my_select"
    label="Select an option"
    placeholder="Choose..."
/>
```

### Multiple Select

```blade
<x-select-vanilla
    :options="[
        'apple' => 'Apple',
        'banana' => 'Banana',
        'cherry' => 'Cherry',
    ]"
    name="fruits"
    label="Select fruits"
    :multiple="true"
/>
```

## Props/Parameters

| Parameter     | Type    | Default              | Description                            |
| ------------- | ------- | -------------------- | -------------------------------------- |
| `options`     | array   | `[]`                 | Array of options (see format below)    |
| `name`        | string  | `null`               | Name attribute for form submission     |
| `label`       | string  | `null`               | Label text above the select            |
| `placeholder` | string  | `'Select an option'` | Placeholder text when nothing selected |
| `id`          | string  | auto-generated       | Unique ID for the select element       |
| `multiple`    | boolean | `false`              | Enable multiple selection              |
| `searchable`  | boolean | `true`               | Enable search/filter functionality     |
| `disabled`    | boolean | `false`              | Disable the select                     |
| `value`       | mixed   | `null`               | Pre-selected value(s)                  |

## Options Format

Options can be provided in two formats:

### Format 1: Key-Value Pairs

```php
[
    'key1' => 'Label 1',
    'key2' => 'Label 2',
    'key3' => 'Label 3',
]
```

### Format 2: Array of Objects

```php
[
    ['value' => '1', 'label' => 'Label 1'],
    ['value' => '2', 'label' => 'Label 2'],
    ['value' => '3', 'label' => 'Label 3'],
]
```

## Examples

### With Pre-selected Value

```blade
<x-select-vanilla
    :options="['red' => 'Red', 'blue' => 'Blue', 'green' => 'Green']"
    name="color"
    value="blue"
/>
```

### Multiple Selection with Pre-selected Values

```blade
<x-select-vanilla
    :options="['1' => 'Option 1', '2' => 'Option 2', '3' => 'Option 3']"
    name="options"
    :multiple="true"
    :value="['1', '3']"
/>
```

### Without Search

```blade
<x-select-vanilla
    :options="['s' => 'Small', 'm' => 'Medium', 'l' => 'Large']"
    name="size"
    :searchable="false"
/>
```

### Disabled State

```blade
<x-select-vanilla
    :options="['1' => 'Option 1', '2' => 'Option 2']"
    name="disabled_select"
    :disabled="true"
/>
```

### Using with Controller Data

```blade
<x-select-vanilla
    :options="$programs"
    name="program_id"
    label="Select Program"
    placeholder="Choose a program..."
/>
```

Where in your controller:

```php
$programs = Program::pluck('name', 'id')->toArray();
// Results in: ['1' => 'Program Name', '2' => 'Another Program', ...]
```

## Form Submission

### Single Select

When submitted, the form will include:

```
name=value
```

Example: `program_id=3`

### Multiple Select

When submitted, the form will include:

```
name[]=value1
name[]=value2
```

Example: `fruits[]=apple&fruits[]=banana`

### Handling in Laravel Controller

```php
// Single select
$value = $request->input('my_select');

// Multiple select
$values = $request->input('fruits'); // Returns array
```

## Programmatic Control (JavaScript API)

Each select exposes a JavaScript API for programmatic control:

```javascript
// Get the API (replace 'my-select-id' with your select's ID)
const selectAPI = window["vanillaSelect_my-select-id"];

// Get current value
const value = selectAPI.getValue();

// Set value
selectAPI.setValue("new-value");

// For multiple select
selectAPI.setValue(["value1", "value2"]);

// Clear selection
selectAPI.setValue(""); // Single
selectAPI.setValue([]); // Multiple

// Update options dynamically
selectAPI.setOptions([
    { value: "1", label: "New Option 1" },
    { value: "2", label: "New Option 2" },
]);

// Add a single option
selectAPI.addOption("3", "New Option 3");

// Clear all options and reset selection
selectAPI.clearOptions();

// Enable/disable the select
selectAPI.disable();
selectAPI.enable();

// Open/close dropdown
selectAPI.open();
selectAPI.close();
```

### Example: Dynamic Control

```blade
<x-select-vanilla
    :options="['js' => 'JavaScript', 'php' => 'PHP', 'py' => 'Python']"
    name="language"
    id="language-select"
/>

<button onclick="setLanguage('py')">Set to Python</button>

<script>
    function setLanguage(lang) {
        window['vanillaSelect_language-select'].setValue(lang);
    }
</script>
```

### Example: Dynamic Options Loading (AJAX)

```blade
<x-select-vanilla
    :options="[]"
    name="section"
    id="section-select"
    label="Select Section"
/>

<script>
    // Load options from API
    fetch('/api/sections')
        .then(response => response.json())
        .then(data => {
            // Update select options
            window['vanillaSelect_section-select'].setOptions(data.sections);
        });
</script>
```

## Styling

The component uses Bootstrap 5 classes and Remixicon icons. Ensure you have:

1. Bootstrap CSS included
2. Remixicon CSS included

```html
<!-- Bootstrap -->
<link
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css"
    rel="stylesheet"
/>

<!-- Remixicon -->
<link
    href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css"
    rel="stylesheet"
/>
```

### Custom Styling

You can add custom classes using Blade attributes:

```blade
<x-select-vanilla
    :options="$options"
    name="my_select"
    class="custom-class"
    data-custom="value"
/>
```

## JavaScript API Reference

Each vanilla select component exposes a comprehensive API through the global window object:

### API Methods

#### `getValue()`

Returns the current selected value(s).

```javascript
const value = window["vanillaSelect_myId"].getValue();
// Single select: returns string or empty string
// Multiple select: returns array
```

#### `setValue(value)`

Sets the selected value(s).

```javascript
// Single select
window["vanillaSelect_myId"].setValue("option1");

// Multiple select
window["vanillaSelect_myId"].setValue(["option1", "option2"]);

// Clear selection
window["vanillaSelect_myId"].setValue(""); // or []
```

#### `setOptions(options)`

Replaces all options with new ones. Accepts an array of option objects.

```javascript
window["vanillaSelect_myId"].setOptions([
    { value: "1", label: "Option 1" },
    { value: "2", label: "Option 2" },
]);
```

#### `addOption(value, label)`

Adds a single option to the existing options list.

```javascript
window["vanillaSelect_myId"].addOption("new-value", "New Option");
```

#### `clearOptions()`

Removes all options and resets the selection.

```javascript
window["vanillaSelect_myId"].clearOptions();
```

#### `enable()`

Enables the select (makes it interactive).

```javascript
window["vanillaSelect_myId"].enable();
```

#### `disable()`

Disables the select and closes the dropdown if open.

```javascript
window["vanillaSelect_myId"].disable();
```

#### `open()`

Opens/toggles the dropdown.

```javascript
window["vanillaSelect_myId"].open();
```

#### `close()`

Closes the dropdown.

```javascript
window["vanillaSelect_myId"].close();
```

## Events

The component dispatches standard `change` events on the hidden input(s), so you can listen for changes:

```javascript
document
    .querySelector('input[name="my_select"]')
    .addEventListener("change", function (e) {
        console.log("Value changed to:", e.target.value);
    });
```

## Differences from Livewire Version

| Feature          | Livewire Version         | Vanilla Version      |
| ---------------- | ------------------------ | -------------------- |
| Framework        | Alpine.js + Livewire     | Vanilla JavaScript   |
| Two-way binding  | ✅ (@entangle)           | ❌ (Use API instead) |
| Form submission  | ✅                       | ✅                   |
| Reactive updates | ✅                       | Via API only         |
| Bundle size      | Larger (requires Alpine) | Smaller (pure JS)    |

## Browser Support

-   Chrome/Edge (latest)
-   Firefox (latest)
-   Safari (latest)
-   Any modern browser supporting ES6+

## Troubleshooting

### Dropdown not showing

-   Ensure Bootstrap CSS is loaded
-   Check z-index conflicts with other elements
-   Verify the dropdown container has proper positioning context

### Search not working

-   Make sure `searchable` is set to `true` (it's true by default)
-   Check browser console for JavaScript errors

### Form not submitting values

-   Verify the `name` attribute is set
-   For multiple selects, ensure you're reading `name[]` as an array
-   Check if the form has proper `action` and `method` attributes

### Styling issues

-   Ensure Remixicon is loaded for arrow icons
-   Check Bootstrap version compatibility (designed for Bootstrap 5)
-   Use browser dev tools to inspect element classes

## License

This component is part of the eSMART project.
