# Icon Component Documentation

## Overview

The icon component provides a consistent way to use Heroicons throughout the SIKOPMA application. It supports multiple sizes, custom colors, and includes a comprehensive set of commonly used icons.

## Basic Usage

```blade
<x-ui.icon name="check-circle" />
```

## Props

### name (required)
The name of the icon to display. See available icons below.

### size (optional)
Controls the size of the icon. Default: `md`

Available sizes:
- `xs` - 12px (w-3 h-3)
- `sm` - 16px (w-4 h-4)
- `md` - 20px (w-5 h-5) - Default
- `lg` - 24px (w-6 h-6)
- `xl` - 32px (w-8 h-8)
- `2xl` - 40px (w-10 h-10)

### color (optional)
Custom stroke color. Default: `currentColor` (inherits from parent text color)

### class (optional)
Additional Tailwind classes can be passed via the `class` attribute.

## Examples

### Basic Icon
```blade
<x-ui.icon name="user" />
```

### Custom Size
```blade
<x-ui.icon name="user" size="lg" />
```

### Custom Color (using Tailwind classes)
```blade
<x-ui.icon name="check-circle" class="text-green-600" />
```

### In a Button
```blade
<button class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg">
    <x-ui.icon name="plus" size="sm" class="mr-2" />
    Add Item
</button>
```

### In an Alert
```blade
<div class="flex items-start p-4 bg-green-50 border border-green-200 rounded-lg">
    <x-ui.icon name="check-circle" class="text-green-600 mr-3 flex-shrink-0" />
    <div>
        <p class="text-sm font-medium text-green-800">Success!</p>
        <p class="text-sm text-green-700">Your changes have been saved.</p>
    </div>
</div>
```

### In a List
```blade
<ul class="space-y-2">
    <li class="flex items-center text-sm text-gray-700">
        <x-ui.icon name="check-circle" size="sm" class="text-green-600 mr-2" />
        Task completed
    </li>
</ul>
```

## Available Icons

### Status Icons
- `check-circle` - Success/completed state
- `x-circle` - Error/failed state
- `exclamation-circle` - Warning state
- `information-circle` - Info state
- `exclamation-triangle` - Alert/warning state

### Navigation Icons
- `chevron-right` - Right chevron
- `chevron-left` - Left chevron
- `chevron-up` - Up chevron
- `chevron-down` - Down chevron
- `arrow-right` - Right arrow
- `arrow-left` - Left arrow
- `arrow-up` - Up arrow
- `arrow-down` - Down arrow

### Action Icons
- `x` - Close/dismiss
- `plus` - Add/create
- `minus` - Remove/subtract
- `pencil` - Edit
- `trash` - Delete
- `eye` - View/show
- `eye-slash` - Hide

### Common UI Icons
- `home` - Home/dashboard
- `user` - Single user
- `users` - Multiple users
- `cog` - Settings
- `bell` - Notifications
- `calendar` - Calendar/date
- `clock` - Time
- `document` - Document/file
- `folder` - Folder/directory
- `inbox` - Inbox/messages
- `chart-bar` - Charts/analytics
- `shopping-cart` - Shopping cart
- `currency-dollar` - Money/currency
- `magnifying-glass` - Search
- `bars-3` - Menu/hamburger
- `ellipsis-vertical` - More options
- `download` - Download
- `upload` - Upload
- `link` - Link/URL

## Testing

To view all available icons and test different sizes and contexts, visit:
```
/test-icons
```

This test page shows:
- All size variants
- All available icons organized by category
- Icons in different contexts (buttons, alerts, lists)

## Notes

- Icons use Heroicons v2 outline style
- All icons are SVG-based for crisp rendering at any size
- Icons inherit color from parent text color by default
- Use Tailwind color utilities (e.g., `text-green-600`) for custom colors
- Icons are responsive and work well at all screen sizes
