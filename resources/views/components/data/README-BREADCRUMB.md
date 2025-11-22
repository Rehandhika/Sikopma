# Breadcrumb Component

## Overview
The breadcrumb component provides navigation context showing the user's current location within the application hierarchy. It follows accessibility best practices and includes responsive spacing.

## Location
`resources/views/components/data/breadcrumb.blade.php`

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `items` | array | `[]` | Array of breadcrumb items with label, url, and optional icon |

### Items Array Structure

Each item in the `items` array should have the following structure:

```php
[
    'label' => 'Item Label',    // Required: The text to display
    'url' => '/path/to/page',   // Optional: URL for the link (null for current page)
    'icon' => 'icon-name',      // Optional: Icon name from the icon component
]
```

## Usage Examples

### Basic Breadcrumb (2 levels)
```blade
<x-data.breadcrumb :items="[
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Dashboard', 'url' => null],
]" />
```

### Multi-level Breadcrumb (3+ levels)
```blade
<x-data.breadcrumb :items="[
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Products', 'url' => '/products'],
    ['label' => 'Electronics', 'url' => '/products/electronics'],
    ['label' => 'Laptops', 'url' => null],
]" />
```

### Breadcrumb with Icons
```blade
<x-data.breadcrumb :items="[
    ['label' => 'Home', 'url' => '/', 'icon' => 'home'],
    ['label' => 'Users', 'url' => '/users', 'icon' => 'users'],
    ['label' => 'Profile', 'url' => null, 'icon' => 'user'],
]" />
```

### Deep Navigation (5+ levels)
```blade
<x-data.breadcrumb :items="[
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Reports', 'url' => '/reports'],
    ['label' => 'Sales', 'url' => '/reports/sales'],
    ['label' => '2024', 'url' => '/reports/sales/2024'],
    ['label' => 'January', 'url' => null],
]" />
```

### Using with Page Header Component
```blade
<x-layout.page-header 
    title="User Profile"
    description="Manage your account settings"
>
    <x-slot:breadcrumbs>
        <x-data.breadcrumb :items="[
            ['label' => 'Home', 'url' => '/'],
            ['label' => 'Settings', 'url' => '/settings'],
            ['label' => 'Profile', 'url' => null],
        ]" />
    </x-slot:breadcrumbs>
</x-layout.page-header>
```

## Features

### Visual Design
- **Links**: Gray-500 text that changes to primary-600 on hover
- **Current Page**: Gray-900 text (darker, no hover effect, not a link)
- **Separator**: Chevron-right icon in gray-400
- **Font**: Small (text-sm) with medium weight (font-medium)
- **Smooth Transitions**: 200ms color transition on hover

### Responsive Behavior
- **Mobile**: Smaller spacing between items (space-x-1)
- **Desktop**: Larger spacing between items (md:space-x-2)
- **Icon Spacing**: Consistent 1.5 margin-right for icons

### Accessibility
- ✓ Uses semantic `<nav>` element
- ✓ Includes `aria-label="Breadcrumb"` for screen readers
- ✓ Uses ordered list `<ol>` for proper hierarchical structure
- ✓ Current page is not a link (proper semantic HTML)
- ✓ Clear visual distinction between links and current page
- ✓ Keyboard navigable (tab through links)
- ✓ Focus states on interactive elements

## Styling

### Default Classes
```
nav: flex
ol: inline-flex items-center space-x-1 md:space-x-2
li: inline-flex items-center
```

### Link Styling
```
text-sm font-medium text-gray-500 hover:text-primary-600 transition-colors duration-200
```

### Current Page Styling
```
text-sm font-medium text-gray-900
```

### Separator Icon
```
w-4 h-4 text-gray-400 mx-1 md:mx-2
```

## Best Practices

### Do's
- ✓ Always include "Home" or root page as the first item
- ✓ Set the last item's `url` to `null` (current page)
- ✓ Keep labels concise and descriptive
- ✓ Use icons sparingly for better readability
- ✓ Limit depth to 5-6 levels maximum for usability

### Don'ts
- ✗ Don't make the current page a clickable link
- ✗ Don't use very long labels (truncate if necessary)
- ✗ Don't skip levels in the hierarchy
- ✗ Don't use breadcrumbs for flat navigation structures
- ✗ Don't include the current page title if it's already in the page header

## Common Patterns

### E-commerce Product Page
```blade
<x-data.breadcrumb :items="[
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Electronics', 'url' => '/category/electronics'],
    ['label' => 'Laptops', 'url' => '/category/electronics/laptops'],
    ['label' => 'MacBook Pro 16', 'url' => null],
]" />
```

### Admin Settings Page
```blade
<x-data.breadcrumb :items="[
    ['label' => 'Dashboard', 'url' => '/dashboard', 'icon' => 'home'],
    ['label' => 'Settings', 'url' => '/settings', 'icon' => 'cog'],
    ['label' => 'User Management', 'url' => null, 'icon' => 'users'],
]" />
```

### Report Details Page
```blade
<x-data.breadcrumb :items="[
    ['label' => 'Home', 'url' => '/'],
    ['label' => 'Reports', 'url' => '/reports'],
    ['label' => 'Attendance', 'url' => '/reports/attendance'],
    ['label' => 'Monthly Report', 'url' => null],
]" />
```

## Testing

To test the breadcrumb component, visit:
```
/demo/breadcrumb
```

This test page includes:
- Simple 2-level breadcrumb
- 3-level breadcrumb
- 4-level breadcrumb (deeper navigation)
- 5-level breadcrumb (very deep navigation)
- Breadcrumb with icons
- Single item breadcrumb
- Long labels test
- Responsive behavior test
- All items as links test
- Mixed icons test

## Requirements Met

This component satisfies the following requirements from the specification:

- **Requirement 1.2**: Provides component library with breadcrumb component
- **Requirement 1.3**: Implements consistent variants and props
- **Requirement 3.1**: Uses only Tailwind utility classes
- **Requirement 7.3**: Provides clear focus states for keyboard navigation
- **Requirement 7.4**: Uses semantic HTML and ARIA attributes

## Related Components

- `x-ui.icon` - Used for separator and optional item icons
- `x-layout.page-header` - Often used together with breadcrumbs
- `x-data.tabs` - Alternative navigation pattern for same-level content

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)
- Mobile browsers (iOS Safari, Chrome Mobile)

## Notes

- The component automatically handles the separator icon between items
- The first item never has a separator before it
- Icons are optional and can be mixed (some items with icons, some without)
- The component is fully responsive with adjusted spacing on mobile devices
- All transitions are smooth and performant (GPU-accelerated)
