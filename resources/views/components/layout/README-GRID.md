# Grid Layout Component

## Overview

The Grid component provides a responsive grid layout system that automatically adjusts column counts based on screen size. It follows a mobile-first approach and implements the design system's responsive breakpoints.

## Component Location

`resources/views/components/layout/grid.blade.php`

## Props

| Prop | Type | Default | Options | Description |
|------|------|---------|---------|-------------|
| `cols` | string | `'1'` | `'1'`, `'2'`, `'3'`, `'4'` | Number of columns at largest breakpoint |
| `gap` | string | `'6'` | `'2'`, `'3'`, `'4'`, `'5'`, `'6'`, `'8'` | Gap size between grid items (Tailwind spacing scale) |

## Responsive Behavior

### Column Configurations

#### cols="1" (Single Column)
- **All breakpoints:** 1 column
- Use for: Full-width content, forms, single-column layouts

#### cols="2" (Two Columns)
- **Mobile (< 768px):** 1 column
- **Tablet (≥ 768px):** 2 columns
- Use for: Side-by-side content, comparison layouts

#### cols="3" (Three Columns)
- **Mobile (< 768px):** 1 column
- **Tablet (≥ 768px):** 2 columns
- **Desktop (≥ 1024px):** 3 columns
- Use for: Card grids, product listings, feature sections

#### cols="4" (Four Columns)
- **Mobile (< 768px):** 1 column
- **Tablet (≥ 768px):** 2 columns
- **Desktop (≥ 1024px):** 3 columns
- **XL Desktop (≥ 1280px):** 4 columns
- Use for: Dense card grids, dashboard widgets, gallery layouts

### Gap Sizes

| Gap Value | Spacing | Pixels |
|-----------|---------|--------|
| `'2'` | 0.5rem | 8px |
| `'3'` | 0.75rem | 12px |
| `'4'` | 1rem | 16px |
| `'5'` | 1.25rem | 20px |
| `'6'` | 1.5rem | 24px (default) |
| `'8'` | 2rem | 32px |

## Usage Examples

### Basic Grid

```blade
<x-layout.grid cols="3" gap="6">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</x-layout.grid>
```

### Product Card Grid

```blade
<x-layout.grid cols="3" gap="6">
    @foreach($products as $product)
        <x-ui.card>
            <h3>{{ $product->name }}</h3>
            <p>{{ $product->description }}</p>
            <p class="font-bold">{{ $product->price }}</p>
        </x-ui.card>
    @endforeach
</x-layout.grid>
```

### Dashboard Stats Grid

```blade
<x-layout.grid cols="4" gap="6">
    <x-layout.stat-card 
        label="Total Users" 
        value="1,234" 
        icon="users"
        iconColor="bg-primary-100"
        iconTextColor="text-primary-600"
    />
    <x-layout.stat-card 
        label="Revenue" 
        value="Rp 5.4M" 
        icon="currency-dollar"
        iconColor="bg-success-100"
        iconTextColor="text-success-600"
    />
    <x-layout.stat-card 
        label="Orders" 
        value="856" 
        icon="shopping-cart"
        iconColor="bg-info-100"
        iconTextColor="text-info-600"
    />
    <x-layout.stat-card 
        label="Pending" 
        value="23" 
        icon="clock"
        iconColor="bg-warning-100"
        iconTextColor="text-warning-600"
    />
</x-layout.grid>
```

### Two-Column Form Layout

```blade
<x-layout.grid cols="2" gap="4">
    <x-ui.input 
        label="First Name" 
        name="first_name" 
        required 
    />
    <x-ui.input 
        label="Last Name" 
        name="last_name" 
        required 
    />
    <x-ui.input 
        label="Email" 
        name="email" 
        type="email" 
        required 
    />
    <x-ui.input 
        label="Phone" 
        name="phone" 
        type="tel" 
    />
</x-layout.grid>
```

### Tight Gap Grid

```blade
<x-layout.grid cols="4" gap="2">
    @foreach($images as $image)
        <img src="{{ $image }}" alt="Gallery image" class="rounded-lg">
    @endforeach
</x-layout.grid>
```

### Wide Gap Grid

```blade
<x-layout.grid cols="3" gap="8">
    <div class="bg-white p-6 rounded-lg shadow">Feature 1</div>
    <div class="bg-white p-6 rounded-lg shadow">Feature 2</div>
    <div class="bg-white p-6 rounded-lg shadow">Feature 3</div>
</x-layout.grid>
```

### With Custom Classes

```blade
<x-layout.grid cols="3" gap="6" class="bg-gray-50 p-6 rounded-lg">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</x-layout.grid>
```

## Requirements Satisfied

- **1.2:** Component follows design system with consistent variants
- **1.3:** Uses pure Tailwind utility classes
- **6.4:** Provides reusable grid layout component
- **11.1:** Mobile-first responsive design (single column on mobile)
- **11.2:** Tablet responsive (2 columns at md breakpoint)
- **11.3:** Desktop responsive (3-4 columns at lg/xl breakpoints)

## Accessibility

- Uses semantic `<div>` with grid layout
- No accessibility concerns as it's a layout component
- Content within grid items should follow accessibility best practices

## Browser Support

- All modern browsers (Chrome, Firefox, Safari, Edge)
- CSS Grid is well-supported across all target browsers
- Responsive breakpoints work consistently

## Testing

To test the grid component:

1. **Visual Testing:**
   - Open `resources/views/components/layout/grid-test.blade.php` in browser
   - Resize browser window to test all breakpoints
   - Verify column counts change at correct breakpoints

2. **Responsive Testing:**
   - Mobile (< 768px): Should show 1 column for cols="2", "3", "4"
   - Tablet (768px - 1023px): Should show 2 columns for cols="2", "3", "4"
   - Desktop (1024px - 1279px): Should show 3 columns for cols="3", "4"
   - XL Desktop (≥ 1280px): Should show 4 columns for cols="4"

3. **Gap Testing:**
   - Verify different gap sizes render correctly
   - Check spacing is consistent across all breakpoints

## Common Use Cases

1. **Dashboard Metrics:** Use `cols="4"` for stat cards
2. **Product Listings:** Use `cols="3"` for product cards
3. **Feature Sections:** Use `cols="3"` for feature highlights
4. **Form Layouts:** Use `cols="2"` for side-by-side form fields
5. **Image Galleries:** Use `cols="4"` with `gap="2"` for tight image grids
6. **Blog Posts:** Use `cols="3"` for blog post cards
7. **Team Members:** Use `cols="4"` for team member cards

## Best Practices

1. **Choose appropriate column count:**
   - Use `cols="4"` for small items (icons, small cards)
   - Use `cols="3"` for medium items (product cards, blog posts)
   - Use `cols="2"` for large items (feature sections, comparisons)
   - Use `cols="1"` for full-width content

2. **Gap sizing:**
   - Use smaller gaps (`gap="2"` or `gap="4"`) for dense layouts
   - Use default gap (`gap="6"`) for most cases
   - Use larger gaps (`gap="8"`) for spacious layouts

3. **Content considerations:**
   - Ensure grid items have consistent heights or use `items-start` class
   - Consider using card components within grid for consistent styling
   - Test with varying content lengths

4. **Performance:**
   - Grid component is lightweight and performant
   - No JavaScript required
   - Pure CSS Grid implementation

## Related Components

- `<x-ui.card>` - Often used as grid items
- `<x-layout.stat-card>` - Dashboard metrics in grids
- `<x-layout.empty-state>` - Show when grid has no items
- `<x-ui.skeleton>` - Loading state for grid items

## Migration from Old Pattern

### Before (Hardcoded Classes)
```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</div>
```

### After (Grid Component)
```blade
<x-layout.grid cols="3" gap="6">
    <div>Item 1</div>
    <div>Item 2</div>
    <div>Item 3</div>
</x-layout.grid>
```

## Notes

- The grid component uses CSS Grid, not Flexbox
- Responsive breakpoints follow Tailwind's default breakpoints
- Gap applies to both horizontal and vertical spacing
- Grid items will automatically wrap to new rows as needed
- Component supports additional classes via `$attributes->merge()`
