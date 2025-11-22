# Badge Component

## Description
Badges are used to display status, labels, tags, or counts. They provide visual indicators for categorization and status information.

## Location
`resources/views/components/ui/badge.blade.php`

## Props

| Prop | Type | Default | Options | Description |
|------|------|---------|---------|-------------|
| variant | string | 'primary' | primary, secondary, success, danger, warning, info, gray | Color variant of the badge |
| size | string | 'md' | sm, md, lg | Size of the badge |
| rounded | boolean | false | true, false | Whether to use pill shape (fully rounded) |

## Usage Examples

### Basic Badge
```blade
<x-ui.badge variant="primary">
    Primary Badge
</x-ui.badge>
```

### All Variants
```blade
<x-ui.badge variant="primary">Primary</x-ui.badge>
<x-ui.badge variant="secondary">Secondary</x-ui.badge>
<x-ui.badge variant="success">Success</x-ui.badge>
<x-ui.badge variant="danger">Danger</x-ui.badge>
<x-ui.badge variant="warning">Warning</x-ui.badge>
<x-ui.badge variant="info">Info</x-ui.badge>
<x-ui.badge variant="gray">Gray</x-ui.badge>
```

### Different Sizes
```blade
<x-ui.badge variant="primary" size="sm">Small</x-ui.badge>
<x-ui.badge variant="primary" size="md">Medium</x-ui.badge>
<x-ui.badge variant="primary" size="lg">Large</x-ui.badge>
```

### Rounded (Pill Shape)
```blade
<x-ui.badge variant="success" rounded>Active</x-ui.badge>
<x-ui.badge variant="danger" rounded>Inactive</x-ui.badge>
```

### Status Indicators
```blade
<!-- User status -->
<x-ui.badge variant="success" size="sm">Active</x-ui.badge>
<x-ui.badge variant="danger" size="sm">Inactive</x-ui.badge>
<x-ui.badge variant="warning" size="sm">Pending</x-ui.badge>

<!-- Order status -->
<x-ui.badge variant="info">Processing</x-ui.badge>
<x-ui.badge variant="success">Completed</x-ui.badge>
<x-ui.badge variant="danger">Cancelled</x-ui.badge>
```

### In Table Context
```blade
<table>
    <tr>
        <td>John Doe</td>
        <td>
            <x-ui.badge variant="primary" size="sm">Admin</x-ui.badge>
        </td>
        <td>
            <x-ui.badge variant="success" size="sm">Active</x-ui.badge>
        </td>
    </tr>
</table>
```

### In Card Context
```blade
<div class="card">
    <div class="flex items-center justify-between">
        <h3>User Profile</h3>
        <x-ui.badge variant="success" size="sm">Verified</x-ui.badge>
    </div>
    <p>User details...</p>
</div>
```

### Multiple Badges (Tags)
```blade
<div class="flex flex-wrap gap-2">
    <x-ui.badge variant="primary" size="sm">CSS</x-ui.badge>
    <x-ui.badge variant="secondary" size="sm">JavaScript</x-ui.badge>
    <x-ui.badge variant="info" size="sm">Tutorial</x-ui.badge>
    <x-ui.badge variant="success" size="sm">Beginner</x-ui.badge>
</div>
```

### With Custom Classes
```blade
<x-ui.badge variant="primary" class="uppercase tracking-wide">
    New
</x-ui.badge>
```

## Color Variants

### Primary
- Background: `bg-primary-100`
- Text: `text-primary-800`
- Border: `border-primary-200`
- Use for: Main actions, primary status

### Secondary
- Background: `bg-secondary-100`
- Text: `text-secondary-800`
- Border: `border-secondary-200`
- Use for: Secondary actions, alternative status

### Success
- Background: `bg-success-50`
- Text: `text-success-700`
- Border: `border-success-200`
- Use for: Success states, active status, completed actions

### Danger
- Background: `bg-danger-50`
- Text: `text-danger-700`
- Border: `border-danger-200`
- Use for: Error states, inactive status, critical alerts

### Warning
- Background: `bg-warning-50`
- Text: `text-warning-700`
- Border: `border-warning-200`
- Use for: Warning states, pending status, attention required

### Info
- Background: `bg-info-50`
- Text: `text-info-700`
- Border: `border-info-200`
- Use for: Informational states, in-progress status

### Gray
- Background: `bg-gray-100`
- Text: `text-gray-700`
- Border: `border-gray-200`
- Use for: Neutral states, disabled status, draft items

## Size Specifications

### Small (sm)
- Padding: `px-2 py-0.5`
- Font size: `text-xs`
- Use for: Table cells, compact layouts, inline text

### Medium (md) - Default
- Padding: `px-2.5 py-1`
- Font size: `text-sm`
- Use for: General purpose, cards, lists

### Large (lg)
- Padding: `px-3 py-1.5`
- Font size: `text-base`
- Use for: Prominent displays, headers, featured content

## Best Practices

### Do's
- Use consistent variants for similar statuses across the application
- Use small size badges in tables and compact layouts
- Use rounded badges for tags and categories
- Keep badge text short and concise (1-2 words)
- Use appropriate color variants that match the semantic meaning

### Don'ts
- Don't use badges for long text (use labels or descriptions instead)
- Don't mix rounded and non-rounded badges in the same context
- Don't use too many different badge variants in one view
- Don't use badges as buttons (use button component instead)
- Don't override the color scheme with custom classes

## Common Use Cases

1. **Status Indicators**: Show user status, order status, task status
2. **Role Labels**: Display user roles, permissions, access levels
3. **Tags**: Categorize content, products, articles
4. **Counts**: Show notification counts, item counts (use with numbers)
5. **Flags**: Highlight new items, featured content, special offers
6. **Categories**: Group items by type, department, classification

## Accessibility

- Uses semantic `<span>` element
- Text color has sufficient contrast with background (WCAG AA compliant)
- Font weight is medium for better readability
- Border provides additional visual distinction

## Related Components

- **Button**: For interactive actions
- **Alert**: For larger status messages
- **Tooltip**: For additional information on hover

## Demo

To view all badge variants and use cases, open:
`resources/views/components/ui/badge-demo.blade.php`

## Requirements Satisfied

- ✅ Requirement 1.2: Component with consistent variants
- ✅ Requirement 1.3: Consistent size options
- ✅ Requirement 10.2: Status display component
