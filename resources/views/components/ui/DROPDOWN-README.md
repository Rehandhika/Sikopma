# Dropdown Component Documentation

## Overview

The dropdown component provides a flexible, accessible dropdown menu with Alpine.js integration. It supports multiple alignment options, width variants, smooth transitions, and click-away functionality.

## Components

### 1. Dropdown Container (`<x-ui.dropdown>`)
The main dropdown wrapper that handles the toggle logic and positioning.

### 2. Dropdown Item (`<x-ui.dropdown-item>`)
Individual items within the dropdown menu, supporting both links and buttons.

---

## Props

### Dropdown Container Props

| Prop | Type | Default | Options | Description |
|------|------|---------|---------|-------------|
| `align` | string | `'right'` | `'left'`, `'right'` | Alignment of the dropdown menu |
| `width` | string | `'48'` | `'48'`, `'56'`, `'64'` | Width of the dropdown menu (in Tailwind units) |

### Dropdown Item Props

| Prop | Type | Default | Options | Description |
|------|------|---------|---------|-------------|
| `href` | string | `null` | Any URL | If provided, renders as `<a>` tag, otherwise `<button>` |
| `icon` | string | `null` | Icon name | Icon to display before the text |

---

## Features

✓ **Alignment Options**: Left or right alignment relative to trigger
✓ **Width Variants**: Three width options (48, 56, 64)
✓ **Click-Away**: Automatically closes when clicking outside
✓ **Smooth Transitions**: Enter/leave animations with opacity and scale
✓ **Icon Support**: Optional icons in dropdown items
✓ **Flexible Items**: Support for both links and buttons
✓ **Custom Styling**: Merge additional classes via attributes
✓ **Alpine.js Integration**: Reactive state management
✓ **Multiple Dropdowns**: Each dropdown works independently

---

## Usage Examples

### Basic Dropdown (Right Aligned)

```blade
<x-ui.dropdown align="right" width="48">
    <x-slot:trigger>
        <x-ui.button variant="primary">
            Options
        </x-ui.button>
    </x-slot:trigger>

    <x-ui.dropdown-item href="#profile">
        Profile
    </x-ui.dropdown-item>
    <x-ui.dropdown-item href="#settings">
        Settings
    </x-ui.dropdown-item>
    <x-ui.dropdown-item href="#logout">
        Logout
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

### Left Aligned Dropdown

```blade
<x-ui.dropdown align="left" width="48">
    <x-slot:trigger>
        <x-ui.button variant="secondary">
            Actions
        </x-ui.button>
    </x-slot:trigger>

    <x-ui.dropdown-item href="#edit">
        Edit
    </x-ui.dropdown-item>
    <x-ui.dropdown-item href="#delete">
        Delete
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

### Dropdown with Icons

```blade
<x-ui.dropdown align="right" width="56">
    <x-slot:trigger>
        <x-ui.button variant="white">
            Menu
        </x-ui.button>
    </x-slot:trigger>

    <x-ui.dropdown-item href="#dashboard" icon="check-circle">
        Dashboard
    </x-ui.dropdown-item>
    <x-ui.dropdown-item href="#inbox" icon="inbox">
        Inbox
    </x-ui.dropdown-item>
    <x-ui.dropdown-item href="#settings" icon="cog">
        Settings
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

### Dropdown with Button Actions (No href)

```blade
<x-ui.dropdown align="right" width="48">
    <x-slot:trigger>
        <x-ui.button variant="outline">
            Actions
        </x-ui.button>
    </x-slot:trigger>

    <x-ui.dropdown-item @click="save()">
        Save
    </x-ui.dropdown-item>
    <x-ui.dropdown-item @click="export()">
        Export
    </x-ui.dropdown-item>
    <x-ui.dropdown-item @click="print()">
        Print
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

### Navigation Dropdown Example

```blade
<div class="flex items-center space-x-4">
    <a href="#" class="hover:text-gray-300">Dashboard</a>
    
    <!-- User Dropdown -->
    <x-ui.dropdown align="right" width="48">
        <x-slot:trigger>
            <button class="flex items-center space-x-2">
                <x-ui.avatar name="John Doe" size="sm" />
                <span>John Doe</span>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>
        </x-slot:trigger>

        <x-ui.dropdown-item href="#profile" icon="user">
            My Profile
        </x-ui.dropdown-item>
        <x-ui.dropdown-item href="#settings" icon="cog">
            Settings
        </x-ui.dropdown-item>
        <div class="border-t border-gray-200 my-1"></div>
        <x-ui.dropdown-item href="#logout" icon="x-circle" class="text-danger-600 hover:bg-danger-50">
            Logout
        </x-ui.dropdown-item>
    </x-ui.dropdown>
</div>
```

### Custom Styling

```blade
<x-ui.dropdown align="right" width="48">
    <x-slot:trigger>
        <button class="px-4 py-2 bg-blue-500 text-white rounded-lg">
            Custom Trigger
        </button>
    </x-slot:trigger>

    <x-ui.dropdown-item href="#" class="text-blue-600 font-semibold">
        Custom Styled Item
    </x-ui.dropdown-item>
    <x-ui.dropdown-item href="#" class="text-danger-600 hover:bg-danger-50">
        Danger Action
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

### Dropdown with Dividers

```blade
<x-ui.dropdown align="right" width="48">
    <x-slot:trigger>
        <x-ui.button variant="primary">Menu</x-ui.button>
    </x-slot:trigger>

    <x-ui.dropdown-item href="#profile">Profile</x-ui.dropdown-item>
    <x-ui.dropdown-item href="#settings">Settings</x-ui.dropdown-item>
    
    <!-- Divider -->
    <div class="border-t border-gray-200 my-1"></div>
    
    <x-ui.dropdown-item href="#help">Help</x-ui.dropdown-item>
    <x-ui.dropdown-item href="#logout">Logout</x-ui.dropdown-item>
</x-ui.dropdown>
```

---

## Width Variants

| Width | Tailwind Class | Approximate Size |
|-------|----------------|------------------|
| `48` | `w-48` | 12rem (192px) |
| `56` | `w-56` | 14rem (224px) |
| `64` | `w-64` | 16rem (256px) |

---

## Alignment Options

### Right Aligned (Default)
- Dropdown menu appears aligned to the right edge of the trigger
- Best for navigation menus in the top-right corner
- Uses `origin-top-right right-0`

### Left Aligned
- Dropdown menu appears aligned to the left edge of the trigger
- Best for action menus on the left side
- Uses `origin-top-left left-0`

---

## Alpine.js Integration

The dropdown uses Alpine.js for state management:

```html
<div x-data="{ open: false }" @click.away="open = false">
    <!-- Trigger toggles open state -->
    <div @click="open = !open">
        {{ $trigger }}
    </div>
    
    <!-- Menu shows/hides based on open state -->
    <div x-show="open" x-transition>
        {{ $slot }}
    </div>
</div>
```

### Key Features:
- `x-data="{ open: false }"`: Initializes dropdown state
- `@click="open = !open"`: Toggles dropdown on trigger click
- `@click.away="open = false"`: Closes dropdown when clicking outside
- `x-show="open"`: Shows/hides menu based on state
- `x-transition`: Adds smooth enter/leave animations

---

## Transitions

The dropdown uses smooth transitions for opening and closing:

**Enter Animation** (200ms):
- Opacity: 0 → 100
- Scale: 95% → 100%

**Leave Animation** (150ms):
- Opacity: 100 → 0
- Scale: 100% → 95%

---

## Accessibility

### Keyboard Navigation
- The dropdown supports standard keyboard navigation
- Tab through items
- Enter/Space to activate items

### Semantic HTML
- Uses `<a>` tags for links (with href)
- Uses `<button>` tags for actions (without href)
- Proper focus states on all interactive elements

### Focus Management
- Focus states are visible with ring utilities
- Hover states provide visual feedback

---

## Best Practices

### 1. Use Appropriate Item Type
```blade
<!-- For navigation -->
<x-ui.dropdown-item href="/profile">Profile</x-ui.dropdown-item>

<!-- For actions -->
<x-ui.dropdown-item @click="handleAction()">Action</x-ui.dropdown-item>
```

### 2. Add Icons for Clarity
```blade
<x-ui.dropdown-item href="#edit" icon="pencil">Edit</x-ui.dropdown-item>
<x-ui.dropdown-item href="#delete" icon="trash">Delete</x-ui.dropdown-item>
```

### 3. Use Dividers for Grouping
```blade
<x-ui.dropdown-item href="#profile">Profile</x-ui.dropdown-item>
<div class="border-t border-gray-200 my-1"></div>
<x-ui.dropdown-item href="#logout">Logout</x-ui.dropdown-item>
```

### 4. Highlight Destructive Actions
```blade
<x-ui.dropdown-item 
    href="#delete" 
    class="text-danger-600 hover:bg-danger-50"
>
    Delete
</x-ui.dropdown-item>
```

### 5. Choose Appropriate Width
- Use `width="48"` for short labels (Profile, Settings)
- Use `width="56"` for medium labels (My Account, Preferences)
- Use `width="64"` for longer labels or descriptions

---

## Common Use Cases

### 1. User Profile Menu
```blade
<x-ui.dropdown align="right" width="48">
    <x-slot:trigger>
        <button class="flex items-center">
            <x-ui.avatar name="{{ auth()->user()->name }}" size="sm" />
        </button>
    </x-slot:trigger>
    
    <x-ui.dropdown-item href="{{ route('profile.edit') }}" icon="user">
        Profile
    </x-ui.dropdown-item>
    <x-ui.dropdown-item href="{{ route('settings') }}" icon="cog">
        Settings
    </x-ui.dropdown-item>
    <div class="border-t border-gray-200 my-1"></div>
    <x-ui.dropdown-item href="{{ route('logout') }}" icon="x-circle">
        Logout
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

### 2. Table Row Actions
```blade
<x-ui.dropdown align="right" width="48">
    <x-slot:trigger>
        <button class="p-2 hover:bg-gray-100 rounded">
            <x-ui.icon name="ellipsis-vertical" class="w-5 h-5" />
        </button>
    </x-slot:trigger>
    
    <x-ui.dropdown-item href="{{ route('edit', $item) }}" icon="pencil">
        Edit
    </x-ui.dropdown-item>
    <x-ui.dropdown-item href="{{ route('view', $item) }}" icon="eye">
        View
    </x-ui.dropdown-item>
    <x-ui.dropdown-item 
        @click="deleteItem({{ $item->id }})"
        icon="trash"
        class="text-danger-600 hover:bg-danger-50"
    >
        Delete
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

### 3. Filter/Sort Menu
```blade
<x-ui.dropdown align="left" width="56">
    <x-slot:trigger>
        <x-ui.button variant="white">
            Sort By
            <x-ui.icon name="chevron-down" class="ml-2 w-4 h-4" />
        </x-ui.button>
    </x-slot:trigger>
    
    <x-ui.dropdown-item @click="sortBy('name')">
        Name
    </x-ui.dropdown-item>
    <x-ui.dropdown-item @click="sortBy('date')">
        Date
    </x-ui.dropdown-item>
    <x-ui.dropdown-item @click="sortBy('status')">
        Status
    </x-ui.dropdown-item>
</x-ui.dropdown>
```

---

## Testing

To test the dropdown component, visit:
```
/demo/dropdown
```

This page includes comprehensive tests for:
- Basic dropdown (right aligned)
- Left aligned dropdown
- Dropdown with icons
- Width variants
- Button actions (no href)
- Navigation dropdown example
- Click-away functionality
- Multiple independent dropdowns

---

## Requirements Satisfied

This component satisfies the following requirements from the specification:

- **Requirement 1.2**: Component library with reusable dropdown component
- **Requirement 1.3**: Consistent variants and props (align, width)
- **Requirement 13.2**: Alpine.js integration for interactivity

---

## Related Components

- **Button Component**: Used as trigger in most examples
- **Icon Component**: Used for icons in dropdown items
- **Avatar Component**: Used in user profile dropdowns

---

## Troubleshooting

### Dropdown Not Opening
- Ensure Alpine.js is loaded (`@vite(['resources/js/app.js'])`)
- Check browser console for JavaScript errors
- Verify `x-data` is properly initialized

### Dropdown Not Closing on Click-Away
- Ensure `@click.away` directive is present
- Check for conflicting click handlers
- Verify Alpine.js version compatibility

### Transitions Not Working
- Ensure Tailwind CSS is properly compiled
- Check that transition classes are not purged
- Verify `x-transition` directives are correct

### Dropdown Positioning Issues
- Check parent container positioning
- Verify `relative` class on dropdown container
- Adjust `align` prop as needed

---

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

Requires Alpine.js v3.x for full functionality.
