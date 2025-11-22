# Alert Component

## Overview

The Alert component provides feedback messages to users with different severity levels. It supports multiple variants (success, danger, warning, info), optional icons, dismissible functionality with Alpine.js, and smooth enter/leave transitions.

## Location

`resources/views/components/ui/alert.blade.php`

## Props

| Prop | Type | Default | Options | Description |
|------|------|---------|---------|-------------|
| `variant` | string | `'info'` | `success`, `danger`, `warning`, `info` | The visual style of the alert |
| `dismissible` | boolean | `false` | `true`, `false` | Whether the alert can be dismissed by the user |
| `icon` | boolean | `true` | `true`, `false` | Whether to show the variant-specific icon |

## Features

- **4 Variants**: Success, danger, warning, and info with appropriate colors
- **Variant-Specific Icons**: Each variant has its own icon (check-circle, x-circle, exclamation-triangle, information-circle)
- **Dismissible**: Optional close button with Alpine.js functionality
- **Smooth Transitions**: Enter and leave animations using Alpine.js transitions
- **Flexible Content**: Supports any content in the slot (text, HTML, lists, buttons)
- **Accessible**: Proper focus states and keyboard navigation for dismiss button
- **Responsive**: Works well in different container widths

## Usage Examples

### Basic Alert

```blade
<x-ui.alert variant="success">
    <strong class="font-semibold">Success!</strong> Your changes have been saved.
</x-ui.alert>
```

### Dismissible Alert

```blade
<x-ui.alert variant="danger" :dismissible="true">
    <strong class="font-semibold">Error!</strong> There was a problem processing your request.
</x-ui.alert>
```

### Alert Without Icon

```blade
<x-ui.alert variant="info" :icon="false">
    This is an informational message without an icon.
</x-ui.alert>
```

### Alert with Complex Content

```blade
<x-ui.alert variant="warning" :dismissible="true">
    <div>
        <strong class="font-semibold">Validation failed!</strong>
        <ul class="mt-2 ml-4 list-disc text-sm">
            <li>Email field is required</li>
            <li>Password must be at least 8 characters</li>
        </ul>
    </div>
</x-ui.alert>
```

### Alert with Action Buttons

```blade
<x-ui.alert variant="success" :dismissible="true">
    <div class="space-y-2">
        <div class="font-semibold">Order Completed!</div>
        <p class="text-sm">Your order has been processed successfully.</p>
        <div class="mt-3 flex gap-3">
            <button class="text-sm font-medium underline hover:no-underline">View Order</button>
            <button class="text-sm font-medium underline hover:no-underline">Track Shipment</button>
        </div>
    </div>
</x-ui.alert>
```

## Variant Details

### Success
- **Background**: Light green (`bg-success-50`)
- **Border**: Green (`border-success-200`)
- **Text**: Dark green (`text-success-800`)
- **Icon**: Check circle (`check-circle`)
- **Icon Color**: Medium green (`text-success-400`)

### Danger
- **Background**: Light red (`bg-danger-50`)
- **Border**: Red (`border-danger-200`)
- **Text**: Dark red (`text-danger-800`)
- **Icon**: X circle (`x-circle`)
- **Icon Color**: Medium red (`text-danger-400`)

### Warning
- **Background**: Light yellow (`bg-warning-50`)
- **Border**: Yellow (`border-warning-200`)
- **Text**: Dark yellow (`text-warning-800`)
- **Icon**: Exclamation triangle (`exclamation-triangle`)
- **Icon Color**: Medium yellow (`text-warning-400`)

### Info
- **Background**: Light blue (`bg-info-50`)
- **Border**: Blue (`border-info-200`)
- **Text**: Dark blue (`text-info-800`)
- **Icon**: Information circle (`information-circle`)
- **Icon Color**: Medium blue (`text-info-400`)

## Transitions

The alert component uses Alpine.js transitions for smooth animations:

**Enter Animation** (300ms):
- Starts: `opacity-0 transform scale-95`
- Ends: `opacity-100 transform scale-100`

**Leave Animation** (200ms):
- Starts: `opacity-100 transform scale-100`
- Ends: `opacity-0 transform scale-95`

## Integration with Livewire

### Flash Messages

```php
// In your Livewire component
public function save()
{
    // ... save logic
    session()->flash('success', 'Data saved successfully!');
}
```

```blade
<!-- In your view -->
@if (session()->has('success'))
    <x-ui.alert variant="success" :dismissible="true">
        {{ session('success') }}
    </x-ui.alert>
@endif

@if (session()->has('error'))
    <x-ui.alert variant="danger" :dismissible="true">
        {{ session('error') }}
    </x-ui.alert>
@endif
```

### Validation Errors

```blade
@if ($errors->any())
    <x-ui.alert variant="danger" :dismissible="true">
        <div>
            <strong class="font-semibold">Please fix the following errors:</strong>
            <ul class="mt-2 ml-4 list-disc text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </x-ui.alert>
@endif
```

## Accessibility

- **Focus Management**: Dismiss button has visible focus ring (`focus:ring-2 focus:ring-primary-500`)
- **Keyboard Support**: Dismiss button is keyboard accessible
- **Color Contrast**: All text colors meet WCAG AA standards
- **Semantic HTML**: Uses appropriate HTML structure

## Customization

### Adding Custom Classes

```blade
<x-ui.alert variant="success" class="mb-6 shadow-lg">
    Custom styled alert
</x-ui.alert>
```

### Programmatic Control

```blade
<div x-data="{ showAlert: true }">
    <button @click="showAlert = true">Show Alert</button>
    
    <div x-show="showAlert">
        <x-ui.alert variant="info" :dismissible="true">
            This alert can be controlled programmatically
        </x-ui.alert>
    </div>
</div>
```

## Best Practices

1. **Use Appropriate Variants**: Choose the variant that matches the message severity
2. **Keep Messages Concise**: Short, clear messages are more effective
3. **Use Dismissible for Non-Critical**: Allow users to dismiss informational messages
4. **Don't Overuse**: Too many alerts can overwhelm users
5. **Position Strategically**: Place alerts near the relevant content or at the top of forms
6. **Provide Actions**: Include action buttons when users need to respond

## Common Use Cases

- Form validation errors
- Success confirmations after actions
- Warning messages before destructive actions
- Informational tips and notifications
- System status messages
- Session flash messages

## Demo

To view all alert variants and configurations, open:
`resources/views/components/ui/alert-demo.blade.php`

## Requirements Met

- ✅ 1.2: Component with consistent variants
- ✅ 1.3: Consistent size and color props
- ✅ 9.1: Consistent color scheme for feedback
- ✅ 9.2: Alert component with variants
- ✅ 13.1: Smooth state transitions
- ✅ 13.2: Enter/leave animations with Alpine.js
