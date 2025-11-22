# Table Components Documentation

## Overview

The table component system provides a flexible and consistent way to display tabular data with support for striped rows, hover effects, and responsive behavior.

## Components

### 1. `<x-data.table>`
Main table wrapper component with responsive horizontal scroll.

**Props:**
- `headers` (array, optional): Array of header labels
- `striped` (boolean, default: true): Enable alternating row colors
- `hoverable` (boolean, default: true): Enable hover effect on rows

**Example:**
```blade
<x-data.table :headers="['Name', 'Email', 'Role']">
    <!-- table rows here -->
</x-data.table>
```

### 2. `<x-data.table-row>`
Table row component with striped and hover support.

**Props:**
- `striped` (boolean, default: true): Enable alternating background colors
- `hoverable` (boolean, default: true): Enable hover effect

**Example:**
```blade
<x-data.table-row>
    <!-- table cells here -->
</x-data.table-row>
```

### 3. `<x-data.table-cell>`
Table cell component that can render as `<td>` or `<th>`.

**Props:**
- `header` (boolean, default: false): Render as `<th>` instead of `<td>`

**Example:**
```blade
<x-data.table-cell>Regular cell</x-data.table-cell>
<x-data.table-cell header>Header cell</x-data.table-cell>
```

## Usage Examples

### Basic Table with Headers
```blade
<x-data.table :headers="['Name', 'Email', 'Status']">
    <x-data.table-row>
        <x-data.table-cell>John Doe</x-data.table-cell>
        <x-data.table-cell>john@example.com</x-data.table-cell>
        <x-data.table-cell>Active</x-data.table-cell>
    </x-data.table-row>
    <x-data.table-row>
        <x-data.table-cell>Jane Smith</x-data.table-cell>
        <x-data.table-cell>jane@example.com</x-data.table-cell>
        <x-data.table-cell>Inactive</x-data.table-cell>
    </x-data.table-row>
</x-data.table>
```

### Non-Striped Table
```blade
<x-data.table :headers="['ID', 'Transaction', 'Amount']" :striped="false">
    <x-data.table-row :striped="false">
        <x-data.table-cell>#001</x-data.table-cell>
        <x-data.table-cell>Payment</x-data.table-cell>
        <x-data.table-cell>$500.00</x-data.table-cell>
    </x-data.table-row>
</x-data.table>
```

### Table Without Headers
```blade
<x-data.table>
    <x-data.table-row>
        <x-data.table-cell header>Label:</x-data.table-cell>
        <x-data.table-cell>Value</x-data.table-cell>
    </x-data.table-row>
</x-data.table>
```

### Table with Custom Content
```blade
<x-data.table :headers="['User', 'Status', 'Actions']">
    <x-data.table-row>
        <x-data.table-cell>John Doe</x-data.table-cell>
        <x-data.table-cell>
            <x-ui.badge variant="success">Active</x-ui.badge>
        </x-data.table-cell>
        <x-data.table-cell>
            <button class="text-primary-600 hover:text-primary-800">Edit</button>
        </x-data.table-cell>
    </x-data.table-row>
</x-data.table>
```

### Table with Custom Classes
```blade
<x-data.table :headers="['Column 1', 'Column 2']" class="border-2 border-primary-200">
    <x-data.table-row>
        <x-data.table-cell class="font-bold">Bold text</x-data.table-cell>
        <x-data.table-cell class="text-success-600">Colored text</x-data.table-cell>
    </x-data.table-row>
</x-data.table>
```

## Features

### ✓ Striped Rows
Alternating row colors (odd: white, even: gray-50) for better readability.

### ✓ Hoverable Rows
Smooth hover effect with gray-100 background and transition animation.

### ✓ Responsive Design
Automatic horizontal scroll on mobile devices using `overflow-x-auto`.

### ✓ Flexible Headers
- Pass headers as array prop
- Or omit headers for custom table structures
- Use `header` prop on table-cell for inline headers

### ✓ Custom Styling
All components support custom classes via the `class` attribute.

### ✓ Performance
Optimized for large datasets with efficient CSS classes.

## Styling Details

### Table Container
- `overflow-x-auto`: Enables horizontal scroll on small screens
- `min-w-full`: Ensures table takes full width
- `divide-y divide-gray-200`: Adds borders between sections

### Table Header
- `bg-gray-50`: Light gray background
- `px-6 py-3`: Consistent padding
- `text-xs font-medium text-gray-500 uppercase tracking-wider`: Header text styling

### Table Rows
- Striped: `odd:bg-white even:bg-gray-50`
- Hoverable: `hover:bg-gray-100 transition-colors`

### Table Cells
- Regular cell: `px-6 py-4 text-sm text-gray-700`
- Header cell: `px-6 py-4 text-left text-sm font-medium text-gray-900`

## Requirements Satisfied

- **1.2**: Component follows design system with consistent variants
- **1.3**: Uses pure Tailwind utility classes
- **6.3**: Provides reusable table component for data display
- **10.1**: Supports striped and hoverable options for better UX
- **11.1**: Responsive with horizontal scroll on mobile
- **11.2**: Works across all breakpoints

## Testing

To test the components, open the test file in your browser:
```
resources/views/components/data/table-test.blade.php
```

The test file includes:
1. Basic table with headers
2. Striped table (default)
3. Non-striped table
4. Hoverable table
5. Non-hoverable table
6. Table without headers
7. Responsive table
8. Large dataset (50 rows)
9. Custom classes
10. Actions column

## Browser Compatibility

- Chrome/Edge: ✓ Full support
- Firefox: ✓ Full support
- Safari: ✓ Full support
- Mobile browsers: ✓ Full support with horizontal scroll
