# Pagination Component

## Overview

The pagination component provides a fully-featured, responsive pagination interface that integrates seamlessly with Laravel's built-in pagination system. It automatically adapts between mobile and desktop layouts and handles various page count scenarios.

## Location

`resources/views/components/data/pagination.blade.php`

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `paginator` | `\Illuminate\Pagination\LengthAwarePaginator` | `null` | Laravel paginator instance from `Model::paginate()` |

## Features

### ✅ Laravel Integration
- Works directly with Laravel's `paginate()` method
- Automatically extracts page information
- Handles URL generation for page links
- Supports query string parameters

### ✅ Responsive Design
- **Mobile (< 640px)**: Simplified Previous/Next buttons with page indicator
- **Desktop (≥ 640px)**: Full pagination with page numbers and result count
- Smooth transitions between layouts

### ✅ Smart Page Display
- Shows all pages for small page counts (1-5 pages)
- Uses ellipsis (...) for large page counts
- Always shows first and last page
- Shows context around current page

### ✅ Accessibility
- Semantic HTML with `<nav>` element
- ARIA labels for screen readers
- `aria-current="page"` for active page
- `aria-disabled` for disabled states
- Keyboard navigation support
- Visible focus states

### ✅ Visual States
- **Active page**: Primary color background
- **Disabled**: Grayed out with cursor-not-allowed
- **Hover**: Subtle color change on interactive elements
- **Focus**: Ring outline for keyboard navigation

## Usage

### Basic Usage

```blade
{{-- In your Livewire component --}}
public function render()
{
    $users = User::paginate(15);
    return view('livewire.users.index', compact('users'));
}

{{-- In your Blade view --}}
<x-data.table :headers="['Name', 'Email', 'Status']">
    @foreach($users as $user)
        <x-data.table-row>
            <x-data.table-cell>{{ $user->name }}</x-data.table-cell>
            <x-data.table-cell>{{ $user->email }}</x-data.table-cell>
            <x-data.table-cell>{{ $user->status }}</x-data.table-cell>
        </x-data.table-row>
    @endforeach
</x-data.table>

<x-data.pagination :paginator="$users" />
```

### With Custom Styling

```blade
{{-- Add custom classes --}}
<x-data.pagination :paginator="$users" class="mt-6 border-t pt-4" />
```

### With Search/Filters

```blade
{{-- Pagination automatically preserves query parameters --}}
public function render()
{
    $users = User::query()
        ->when($this->search, fn($q) => $q->where('name', 'like', "%{$this->search}%"))
        ->paginate(15);
    
    return view('livewire.users.index', compact('users'));
}

{{-- The pagination links will include ?search=... parameter --}}
<x-data.pagination :paginator="$users" />
```

### Different Page Sizes

```blade
{{-- 10 items per page --}}
$items = Model::paginate(10);

{{-- 25 items per page --}}
$items = Model::paginate(25);

{{-- 50 items per page --}}
$items = Model::paginate(50);

<x-data.pagination :paginator="$items" />
```

## Responsive Behavior

### Mobile View (< 640px)

```
┌─────────────────────────────────────┐
│ [Previous]  Page 1 of 5  [Next]    │
└─────────────────────────────────────┘
```

Features:
- Simple Previous/Next buttons
- Current page indicator
- Full-width layout
- Touch-friendly button sizes

### Desktop View (≥ 640px)

```
┌──────────────────────────────────────────────────────────┐
│ Showing 1 to 15 of 150 results                           │
│                                                           │
│ [<] [1] [2] [3] [...] [10] [>]                          │
└──────────────────────────────────────────────────────────┘
```

Features:
- Result count display
- Page number buttons
- Ellipsis for large page ranges
- Previous/Next arrow buttons

## Page Display Logic

### Few Pages (1-5)
Shows all page numbers without ellipsis:
```
[<] [1] [2] [3] [4] [5] [>]
```

### Medium Pages (6-10)
Shows pages with ellipsis when needed:
```
[<] [1] [...] [4] [5] [6] [...] [10] [>]
```

### Many Pages (10+)
Shows first few, current context, and last:
```
[<] [1] [2] [3] [...] [20] [>]  (on page 1)
[<] [1] [...] [9] [10] [11] [...] [20] [>]  (on page 10)
```

## Styling

### Color Scheme

- **Active page**: `bg-primary-600` (primary brand color)
- **Inactive pages**: `bg-white` with `text-gray-700`
- **Disabled**: `text-gray-500` with `cursor-not-allowed`
- **Hover**: `hover:text-gray-500` / `hover:bg-gray-100`

### Spacing

- Button padding: `px-4 py-2` for page numbers
- Icon padding: `px-2 py-2` for arrows
- Border radius: `rounded-lg` for container, `rounded-l-lg`/`rounded-r-lg` for edges

### Transitions

- Duration: `150ms` - `200ms`
- Easing: `ease-in-out`
- Properties: color, background-color, opacity

## Accessibility

### Keyboard Navigation

1. **Tab**: Move between page links
2. **Enter/Space**: Activate link
3. **Shift+Tab**: Move backwards

### Screen Reader Support

```html
<!-- Navigation landmark -->
<nav role="navigation" aria-label="Pagination Navigation">

<!-- Current page indicator -->
<span aria-current="page">1</span>

<!-- Disabled state -->
<span aria-disabled="true" aria-label="Previous">

<!-- Page link -->
<a href="..." aria-label="Go to page 2">2</a>
```

### Focus States

All interactive elements have visible focus rings:
```css
focus:outline-none 
focus:ring-2 
focus:ring-primary-500 
focus:border-primary-500
```

## Integration Examples

### With Data Table

```blade
<div class="bg-white rounded-lg shadow-md overflow-hidden">
    <x-data.table :headers="['ID', 'Name', 'Email', 'Created']">
        @foreach($users as $user)
            <x-data.table-row>
                <x-data.table-cell>{{ $user->id }}</x-data.table-cell>
                <x-data.table-cell>{{ $user->name }}</x-data.table-cell>
                <x-data.table-cell>{{ $user->email }}</x-data.table-cell>
                <x-data.table-cell>{{ $user->created_at->format('Y-m-d') }}</x-data.table-cell>
            </x-data.table-row>
        @endforeach
    </x-data.table>
    
    <div class="px-6 py-4 border-t border-gray-200">
        <x-data.pagination :paginator="$users" />
    </div>
</div>
```

### With Card Grid

```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    @foreach($products as $product)
        <x-ui.card>
            <h3>{{ $product->name }}</h3>
            <p>{{ $product->description }}</p>
        </x-ui.card>
    @endforeach
</div>

<div class="mt-6">
    <x-data.pagination :paginator="$products" />
</div>
```

### With Empty State

```blade
@if($users->isEmpty())
    <x-layout.empty-state
        icon="users"
        title="No users found"
        description="Get started by creating your first user."
    >
        <x-slot:action>
            <x-ui.button variant="primary" wire:click="$dispatch('openModal', 'create-user')">
                Add User
            </x-ui.button>
        </x-slot:action>
    </x-layout.empty-state>
@else
    <x-data.table :headers="['Name', 'Email']">
        @foreach($users as $user)
            {{-- table rows --}}
        @endforeach
    </x-data.table>
    
    <x-data.pagination :paginator="$users" class="mt-4" />
@endif
```

## Customization

### Custom Per-Page Options

```blade
{{-- In your Livewire component --}}
public $perPage = 15;

public function render()
{
    $users = User::paginate($this->perPage);
    return view('livewire.users.index', compact('users'));
}

{{-- In your view --}}
<div class="flex items-center justify-between mb-4">
    <select wire:model.live="perPage" class="rounded-lg border-gray-300">
        <option value="10">10 per page</option>
        <option value="25">25 per page</option>
        <option value="50">50 per page</option>
        <option value="100">100 per page</option>
    </select>
</div>

<x-data.pagination :paginator="$users" />
```

### Custom Page Name

```blade
{{-- Use custom query parameter name --}}
$users = User::paginate(15, ['*'], 'user_page');

{{-- URL will be: ?user_page=2 instead of ?page=2 --}}
```

## Testing

### Test File Location

`resources/views/components/data/pagination-test.blade.php`

### Running Tests

1. Create a route to the test file:
```php
Route::get('/test/pagination', function () {
    return view('components.data.pagination-test');
});
```

2. Visit `/test/pagination` in your browser

3. Test scenarios:
   - Resize browser to test responsive behavior
   - Check keyboard navigation (Tab through links)
   - Verify focus states are visible
   - Test on different browsers

## Browser Support

- ✅ Chrome (latest)
- ✅ Firefox (latest)
- ✅ Safari (latest)
- ✅ Edge (latest)
- ✅ Mobile browsers (iOS Safari, Chrome Mobile)

## Performance

- **Minimal CSS**: Uses only Tailwind utility classes
- **No JavaScript**: Pure HTML/CSS implementation
- **Fast rendering**: Efficient Blade template
- **SEO-friendly**: Server-side rendered links

## Related Components

- `<x-data.table>` - Data table component
- `<x-data.table-row>` - Table row component
- `<x-data.table-cell>` - Table cell component
- `<x-layout.empty-state>` - Empty state component

## Requirements Met

This component satisfies the following requirements from the specification:

- **Requirement 1.2**: Component follows design system with consistent variants
- **Requirement 1.3**: Uses pure Tailwind utility classes
- **Responsive behavior**: Mobile-first approach with breakpoint-specific layouts
- **Accessibility**: Semantic HTML, ARIA attributes, keyboard navigation
- **Laravel integration**: Works seamlessly with `paginate()` method

## Notes

- The component only renders if the paginator has pages (`$paginator->hasPages()`)
- If there's only one page, the pagination won't display
- Query parameters are automatically preserved in pagination links
- The component uses Laravel's built-in pagination view structure
- SVG icons are inline for better performance (no external icon library needed)

## Troubleshooting

### Pagination not showing

**Problem**: Component doesn't render
**Solution**: Ensure you're passing a paginator object, not a collection:
```blade
{{-- Wrong --}}
$users = User::all();

{{-- Correct --}}
$users = User::paginate(15);
```

### Styles not applying

**Problem**: Pagination looks unstyled
**Solution**: Ensure Tailwind CSS is compiled and loaded:
```bash
npm run dev
```

### Links not working in Livewire

**Problem**: Clicking pagination links causes full page reload
**Solution**: This is expected behavior. For AJAX pagination, use Livewire's `wire:click`:
```blade
{{-- Custom Livewire pagination --}}
<button wire:click="gotoPage({{ $page }})">{{ $page }}</button>
```

## Future Enhancements

Potential improvements for future versions:

- [ ] AJAX pagination option for Livewire
- [ ] Customizable page range (how many pages to show)
- [ ] Jump to page input
- [ ] Customizable text labels (internationalization)
- [ ] Dark mode support
- [ ] Animation options for page transitions
