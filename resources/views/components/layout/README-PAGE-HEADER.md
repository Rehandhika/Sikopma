# Page Header Component

## Overview

The `page-header` component provides a consistent, responsive header for pages throughout the application. It supports titles, descriptions, breadcrumb navigation, and action buttons.

## Location

`resources/views/components/layout/page-header.blade.php`

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | string | `''` | The main page title (required) |
| `description` | string\|null | `null` | Optional description text below the title |
| `breadcrumbs` | array | `[]` | Array of breadcrumb items for navigation |

## Slots

| Slot | Description |
|------|-------------|
| `actions` | Optional slot for action buttons (e.g., "Add", "Export", "Filter") |

## Breadcrumbs Format

Breadcrumbs should be passed as an array of associative arrays:

```php
[
    ['label' => 'Home', 'url' => '/'],           // Clickable link
    ['label' => 'Settings', 'url' => '/settings'], // Clickable link
    ['label' => 'Profile']                        // Current page (no URL)
]
```

- Items with `url` key will be rendered as clickable links
- Items without `url` key will be rendered as plain text (current page)
- Chevron icons automatically separate breadcrumb items

## Usage Examples

### Basic Title Only

```blade
<x-layout.page-header 
    title="Dashboard"
/>
```

### Title with Description

```blade
<x-layout.page-header 
    title="User Management"
    description="Manage user accounts, roles, and permissions"
/>
```

### With Breadcrumbs

```blade
<x-layout.page-header 
    title="Edit Profile"
    description="Update your personal information"
    :breadcrumbs="[
        ['label' => 'Home', 'url' => route('home')],
        ['label' => 'Settings', 'url' => route('settings.general')],
        ['label' => 'Profile']
    ]"
/>
```

### With Action Buttons

```blade
<x-layout.page-header 
    title="Products"
    description="Manage your product catalog"
>
    <x-slot:actions>
        <x-ui.button variant="white" size="md">
            Export
        </x-ui.button>
        <x-ui.button variant="primary" size="md">
            Add Product
        </x-ui.button>
    </x-slot:actions>
</x-layout.page-header>
```

### Complete Example (All Features)

```blade
<x-layout.page-header 
    title="Sales Report"
    description="View and analyze sales data for the current period"
    :breadcrumbs="[
        ['label' => 'Dashboard', 'url' => route('dashboard')],
        ['label' => 'Reports', 'url' => route('reports.index')],
        ['label' => 'Sales']
    ]"
>
    <x-slot:actions>
        <x-ui.button variant="white" size="md">
            <x-ui.icon name="download" class="w-4 h-4 mr-2" />
            Download
        </x-ui.button>
        <x-ui.button variant="primary" size="md">
            <x-ui.icon name="filter" class="w-4 h-4 mr-2" />
            Filter
        </x-ui.button>
    </x-slot:actions>
</x-layout.page-header>
```

### With Multiple Actions

```blade
<x-layout.page-header 
    title="Attendance Management"
>
    <x-slot:actions>
        <x-ui.button variant="white" size="sm">
            Export CSV
        </x-ui.button>
        <x-ui.button variant="white" size="sm">
            Print
        </x-ui.button>
        <x-ui.button variant="primary" size="sm">
            New Entry
        </x-ui.button>
    </x-slot:actions>
</x-layout.page-header>
```

## Responsive Behavior

### Mobile (< 640px)
- Title and actions stack vertically
- Title truncates if too long
- Breadcrumbs wrap to multiple lines if needed
- Actions take full width with proper spacing

### Tablet (640px - 1024px)
- Title and actions appear side-by-side
- Responsive font sizes apply
- Proper spacing maintained

### Desktop (> 1024px)
- Full horizontal layout
- Larger title font size (3xl)
- Actions aligned to the right

## Accessibility Features

- Semantic HTML structure with proper heading hierarchy
- Breadcrumb navigation uses `<nav>` with `aria-label="Breadcrumb"`
- Current page in breadcrumbs marked with `aria-current="page"`
- Focus states on all interactive elements
- Proper color contrast ratios

## Styling Details

### Title
- Font: Bold, 2xl on mobile, 3xl on desktop
- Color: Gray-900
- Truncates with ellipsis on overflow

### Description
- Font: Regular, sm
- Color: Gray-600
- Margin-top: 0.5rem

### Breadcrumbs
- Font: Regular, sm
- Link color: Gray-500, hover Gray-700
- Current page: Gray-900, medium weight
- Separator: Chevron-right icon, Gray-400

### Actions Container
- Flexbox with 0.75rem gap between buttons
- Shrinks to prevent overflow
- Stacks on mobile, inline on desktop

## Dependencies

- `x-ui.icon` component for breadcrumb separators
- Tailwind CSS for styling
- Alpine.js (if actions contain interactive elements)

## Design System Compliance

This component follows the SIKOPMA design system:
- Uses design tokens from `tailwind.config.js`
- Consistent spacing scale
- Responsive breakpoints (sm: 640px)
- Typography scale
- Color palette (gray scale)

## Testing

Test file available at: `resources/views/components/layout/page-header-test.blade.php`

Access via route: `/demo/page-header`

### Test Coverage
1. Basic title only
2. Title with description
3. With breadcrumbs
4. With action buttons
5. Complete (all features)
6. Long title (truncation test)
7. Multiple actions

### Manual Testing Checklist
- [ ] Title displays correctly
- [ ] Description appears when provided
- [ ] Breadcrumbs render with proper separators
- [ ] Breadcrumb links are clickable
- [ ] Current page in breadcrumbs is not clickable
- [ ] Action buttons appear in actions slot
- [ ] Responsive layout works on mobile (< 640px)
- [ ] Responsive layout works on tablet (640px - 1024px)
- [ ] Responsive layout works on desktop (> 1024px)
- [ ] Long titles truncate properly
- [ ] Multiple actions maintain proper spacing
- [ ] Keyboard navigation works
- [ ] Focus states are visible

## Requirements Satisfied

This component satisfies the following requirements from the specification:

- **1.2**: Component uses consistent Tailwind utility classes
- **1.3**: Follows design system patterns with proper variants
- **6.1**: Provides page header with title, description, and actions
- **11.1**: Responsive on mobile (< 768px) with stacked layout
- **11.2**: Responsive on tablet/desktop with horizontal layout

## Migration Guide

### Before (Hardcoded HTML)
```blade
<div class="mb-6">
    <h1 class="text-3xl font-bold">{{ $title }}</h1>
    <p class="text-gray-600">{{ $description }}</p>
</div>
```

### After (Using Component)
```blade
<x-layout.page-header 
    :title="$title"
    :description="$description"
/>
```

## Notes

- The component uses `mb-6` for bottom margin by default
- Title uses `truncate` class to prevent overflow on mobile
- Actions use `flex-shrink-0` to prevent compression
- Breadcrumbs use semantic `<nav>` and `<ol>` elements
- All transitions use Tailwind's `transition-colors` utility
