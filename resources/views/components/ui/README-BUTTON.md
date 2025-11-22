# Button Component

## Description
The button component is a versatile, reusable component that supports multiple variants, sizes, states, and can be used as both a button and a link.

## Location
`resources/views/components/ui/button.blade.php`

## Props

| Prop | Type | Default | Options | Description |
|------|------|---------|---------|-------------|
| `variant` | string | `'primary'` | `primary`, `secondary`, `success`, `danger`, `warning`, `info`, `white`, `outline`, `ghost` | The visual style variant of the button |
| `size` | string | `'md'` | `sm`, `md`, `lg` | The size of the button |
| `type` | string | `'button'` | `button`, `submit`, `reset` | The HTML button type (only applies when not using href) |
| `loading` | boolean | `false` | `true`, `false` | Shows a spinner and disables the button |
| `disabled` | boolean | `false` | `true`, `false` | Disables the button |
| `icon` | string | `null` | Any valid icon name from icon component | Shows an icon before the button text |
| `href` | string | `null` | Any valid URL | Renders the button as an anchor tag instead of a button |

## Variants

### Primary
Default brand color button for primary actions.
```blade
<x-ui.button variant="primary">Primary Button</x-ui.button>
```

### Secondary
Secondary brand color for less prominent actions.
```blade
<x-ui.button variant="secondary">Secondary Button</x-ui.button>
```

### Success
Green button for positive actions (save, confirm, etc.).
```blade
<x-ui.button variant="success">Success Button</x-ui.button>
```

### Danger
Red button for destructive actions (delete, cancel, etc.).
```blade
<x-ui.button variant="danger">Danger Button</x-ui.button>
```

### Warning
Orange/yellow button for warning actions.
```blade
<x-ui.button variant="warning">Warning Button</x-ui.button>
```

### Info
Blue button for informational actions.
```blade
<x-ui.button variant="info">Info Button</x-ui.button>
```

### White
White button with border for use on colored backgrounds.
```blade
<x-ui.button variant="white">White Button</x-ui.button>
```

### Outline
Transparent button with colored border and text.
```blade
<x-ui.button variant="outline">Outline Button</x-ui.button>
```

### Ghost
Transparent button with no border, shows background on hover.
```blade
<x-ui.button variant="ghost">Ghost Button</x-ui.button>
```

## Sizes

### Small
```blade
<x-ui.button size="sm">Small Button</x-ui.button>
```

### Medium (Default)
```blade
<x-ui.button size="md">Medium Button</x-ui.button>
```

### Large
```blade
<x-ui.button size="lg">Large Button</x-ui.button>
```

## Usage Examples

### Basic Button
```blade
<x-ui.button variant="primary">
    Click Me
</x-ui.button>
```

### Button with Icon
```blade
<x-ui.button variant="primary" icon="plus">
    Add Item
</x-ui.button>
```

### Loading Button
```blade
<x-ui.button variant="primary" :loading="$isLoading">
    Submit
</x-ui.button>
```

### Disabled Button
```blade
<x-ui.button variant="primary" :disabled="true">
    Disabled
</x-ui.button>
```

### Submit Button in Form
```blade
<form wire:submit.prevent="save">
    <x-ui.button variant="success" type="submit">
        Save Changes
    </x-ui.button>
</form>
```

### Button as Link
```blade
<x-ui.button variant="primary" href="{{ route('dashboard') }}">
    Go to Dashboard
</x-ui.button>
```

### Button with Custom Classes
```blade
<x-ui.button variant="primary" class="w-full">
    Full Width Button
</x-ui.button>
```

### Combination Examples
```blade
<!-- Small button with icon -->
<x-ui.button variant="success" size="sm" icon="check-circle">
    Approve
</x-ui.button>

<!-- Large loading button -->
<x-ui.button variant="primary" size="lg" :loading="$processing">
    Processing...
</x-ui.button>

<!-- Outline button with icon -->
<x-ui.button variant="outline" icon="pencil">
    Edit
</x-ui.button>

<!-- Danger button with icon -->
<x-ui.button variant="danger" icon="trash">
    Delete
</x-ui.button>
```

## Livewire Integration

### With Wire Click
```blade
<x-ui.button variant="primary" wire:click="save">
    Save
</x-ui.button>
```

### With Loading State
```blade
<x-ui.button 
    variant="primary" 
    wire:click="save" 
    :loading="$isSaving"
>
    Save
</x-ui.button>
```

### With Confirmation
```blade
<x-ui.button 
    variant="danger" 
    wire:click="delete" 
    wire:confirm="Are you sure you want to delete this item?"
    icon="trash"
>
    Delete
</x-ui.button>
```

## Accessibility

- Uses semantic `<button>` or `<a>` elements
- Includes proper focus states with visible focus ring
- Disabled state prevents interaction and is properly communicated
- Loading state includes visual spinner feedback
- Supports keyboard navigation
- Color contrast meets WCAG AA standards

## Dependencies

- `resources/views/components/ui/icon.blade.php` - For icon support
- `resources/views/components/ui/spinner.blade.php` - For loading state

## Styling

The button uses Tailwind CSS utility classes with the following features:
- Smooth transitions (200ms)
- Focus ring with offset
- Hover states for all variants
- Disabled state with reduced opacity
- Responsive sizing
- Consistent padding and spacing

## Testing

To test all button variants, visit the demo page:
```
/demo/button
```

Or create a test in your view:
```blade
<!-- Test all variants -->
<div class="flex gap-2">
    <x-ui.button variant="primary">Primary</x-ui.button>
    <x-ui.button variant="secondary">Secondary</x-ui.button>
    <x-ui.button variant="success">Success</x-ui.button>
    <x-ui.button variant="danger">Danger</x-ui.button>
    <x-ui.button variant="warning">Warning</x-ui.button>
    <x-ui.button variant="info">Info</x-ui.button>
    <x-ui.button variant="white">White</x-ui.button>
    <x-ui.button variant="outline">Outline</x-ui.button>
    <x-ui.button variant="ghost">Ghost</x-ui.button>
</div>
```

## Notes

- When `loading` is true, the button is automatically disabled
- When `href` is provided and the button is not disabled/loading, it renders as an `<a>` tag
- The spinner color is automatically set to white for better visibility on colored backgrounds
- Icon size is fixed at `w-5 h-5` for consistency across all button sizes
- All additional attributes are merged with the component's default classes
