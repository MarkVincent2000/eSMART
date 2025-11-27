# Toast Notification Component

A reusable Blade component for creating toast notifications using Toastify JS, based on the Display Position examples from `ui-notifications.blade.php`.

## Component File

- **Location**: `resources/views/components/toast-notification.blade.php`
- **Component Name**: `toast-notification`
- **Usage**: `<x-toast-notification>...</x-toast-notification>`

## Basic Usage

```blade
<x-toast-notification 
    text="Welcome Back! This is a Toast Notification" 
    gravity="top" 
    position="right"
>
    Show Toast
</x-toast-notification>
```

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `text` | string | `'This is a Toast Notification'` | The toast notification message |
| `gravity` | string | `'top'` | Vertical position: `'top'` or `'bottom'` |
| `position` | string | `'right'` | Horizontal position: `'left'`, `'center'`, or `'right'` |
| `duration` | integer | `3000` | Display duration in milliseconds (use `-1` for persistent) |
| `className` | string | `'primary'` | Toast style: `'primary'`, `'success'`, `'warning'`, or `'danger'` |
| `close` | boolean | `true` | Show close button |
| `offset` | boolean | `false` | Add offset to position |
| `style` | boolean | `false` | Apply custom style |
| `tag` | string | `'button'` | HTML tag to use: `'button'`, `'div'`, `'a'`, etc. |
| `class` | string | `'btn btn-light w-xs'` | Additional CSS classes |
| `type` | string | `'button'` | Button type when tag is `'button'` |

## Display Position Examples

### All Positions

```blade
{{-- Top Left --}}
<x-toast-notification 
    text="Welcome Back ! This is a Toast Notification" 
    gravity="top" 
    position="left" 
    duration="3000" 
    :close="true"
>
    Top Left
</x-toast-notification>

{{-- Top Center --}}
<x-toast-notification 
    text="Welcome Back ! This is a Toast Notification" 
    gravity="top" 
    position="center" 
    duration="3000" 
    :close="true"
>
    Top Center
</x-toast-notification>

{{-- Top Right --}}
<x-toast-notification 
    text="Welcome Back ! This is a Toast Notification" 
    gravity="top" 
    position="right" 
    duration="3000" 
    :close="true"
>
    Top Right
</x-toast-notification>

{{-- Bottom Left --}}
<x-toast-notification 
    text="Welcome Back ! This is a Toast Notification" 
    gravity="bottom" 
    position="left" 
    duration="3000" 
    :close="true"
>
    Bottom Left
</x-toast-notification>

{{-- Bottom Center --}}
<x-toast-notification 
    text="Welcome Back ! This is a Toast Notification" 
    gravity="bottom" 
    position="center" 
    duration="3000" 
    :close="true"
>
    Bottom Center
</x-toast-notification>

{{-- Bottom Right --}}
<x-toast-notification 
    text="Welcome Back ! This is a Toast Notification" 
    gravity="bottom" 
    position="right" 
    duration="3000" 
    :close="true"
>
    Bottom Right
</x-toast-notification>
```

## Toast Types

### Primary Toast
```blade
<x-toast-notification 
    text="Welcome Back! This is a Toast Notification" 
    gravity="top" 
    position="right" 
    className="primary" 
    duration="3000" 
    :close="true"
>
    Primary
</x-toast-notification>
```

### Success Toast
```blade
<x-toast-notification 
    text="Your application was successfully sent" 
    gravity="top" 
    position="center" 
    className="success" 
    duration="3000"
>
    Success
</x-toast-notification>
```

### Warning Toast
```blade
<x-toast-notification 
    text="Warning ! Something went wrong try again" 
    gravity="top" 
    position="center" 
    className="warning" 
    duration="3000"
>
    Warning
</x-toast-notification>
```

### Danger Toast
```blade
<x-toast-notification 
    text="Error ! An error occurred." 
    gravity="top" 
    position="center" 
    className="danger" 
    duration="3000"
>
    Error
</x-toast-notification>
```

## Advanced Options

### Offset Position
```blade
<x-toast-notification 
    text="Welcome Back ! This is a Toast Notification" 
    gravity="top" 
    position="right" 
    duration="3000" 
    :offset="true" 
    :close="true"
>
    Click Me
</x-toast-notification>
```

### Custom Duration
```blade
<x-toast-notification 
    text="Toast Duration 5s" 
    gravity="top" 
    position="right" 
    duration="5000"
>
    Click Me
</x-toast-notification>
```

### Custom Button Styling
```blade
<x-toast-notification 
    text="Custom styled button" 
    gravity="top" 
    position="right" 
    className="success"
    class="btn btn-success btn-sm"
>
    Custom Button
</x-toast-notification>
```

### Using Different HTML Tags
```blade
{{-- As a link --}}
<x-toast-notification 
    text="Click this link" 
    gravity="top" 
    position="right" 
    tag="a"
    class="btn btn-link text-primary"
    :close="false"
>
    Link Style
</x-toast-notification>

{{-- As a div --}}
<x-toast-notification 
    text="Div element" 
    gravity="top" 
    position="right" 
    tag="div"
    class="btn btn-outline-primary"
>
    Div Element
</x-toast-notification>
```

## Component Structure

The component generates HTML with the following data attributes that work with Toastify JS:

- `data-toast` - Enables toast functionality
- `data-toast-text` - The notification message
- `data-toast-gravity` - Vertical position (top/bottom)
- `data-toast-position` - Horizontal position (left/center/right)
- `data-toast-duration` - Display duration
- `data-toast-className` - Toast style class
- `data-toast-close` - Close button option
- `data-toast-offset` - Offset positioning
- `data-toast-style` - Custom style option

## Example File

See `resources/views/components/toast-notification-example.blade.php` for complete usage examples.

## Notes

- This component requires Toastify JS to be loaded in your layout/scripts
- The component is based on the Display Position examples from `resources/views/ui-notifications.blade.php`
- All text values are automatically escaped for security using `htmlspecialchars()`
- Boolean props should be passed using `:close="true"` syntax in Blade templates


