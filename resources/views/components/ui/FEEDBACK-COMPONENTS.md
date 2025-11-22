# Feedback Components Documentation

This document describes the three feedback components: Spinner, Skeleton, and Avatar.

## Spinner Component

The spinner component displays an animated loading indicator.

### Props

- `size`: sm, md, lg (default: md)
- `color`: primary, white, gray (default: primary)

### Usage Examples

```blade
<!-- Basic spinner -->
<x-ui.spinner />

<!-- Small spinner -->
<x-ui.spinner size="sm" />

<!-- White spinner (for dark backgrounds) -->
<x-ui.spinner color="white" />

<!-- In a button -->
<button class="inline-flex items-center px-4 py-2 bg-primary-600 text-white rounded-lg">
    <x-ui.spinner size="sm" color="white" class="mr-2" />
    Loading...
</button>
```

## Skeleton Component

The skeleton component displays a placeholder for content that is loading.

### Props

- `type`: text, circle, rectangle (default: text)
- `width`: Tailwind width class (default: w-full)
- `height`: Tailwind height class (default: h-4)

### Usage Examples

```blade
<!-- Text skeleton -->
<x-ui.skeleton type="text" width="w-full" height="h-4" />

<!-- Circle skeleton (for avatars) -->
<x-ui.skeleton type="circle" width="w-10" height="h-10" />

<!-- Rectangle skeleton (for images) -->
<x-ui.skeleton type="rectangle" width="w-full" height="h-32" />

<!-- Loading card example -->
<div class="border border-gray-200 rounded-lg p-4 space-y-3">
    <div class="flex items-center space-x-3">
        <x-ui.skeleton type="circle" width="w-12" height="h-12" />
        <div class="flex-1 space-y-2">
            <x-ui.skeleton type="text" width="w-1/3" height="h-4" />
            <x-ui.skeleton type="text" width="w-1/4" height="h-3" />
        </div>
    </div>
    <x-ui.skeleton type="rectangle" width="w-full" height="h-40" />
</div>
```

## Avatar Component

The avatar component displays a user avatar with image or initials fallback.

### Props

- `src`: Image URL (optional)
- `name`: User name for initials fallback (required)
- `size`: sm, md, lg, xl (default: md)

### Usage Examples

```blade
<!-- Avatar with initials -->
<x-ui.avatar name="John Doe" />

<!-- Avatar with image -->
<x-ui.avatar 
    src="https://example.com/avatar.jpg" 
    name="John Doe" 
/>

<!-- Different sizes -->
<x-ui.avatar name="John Doe" size="sm" />
<x-ui.avatar name="John Doe" size="md" />
<x-ui.avatar name="John Doe" size="lg" />
<x-ui.avatar name="John Doe" size="xl" />

<!-- In a user list -->
<div class="flex items-center space-x-3">
    <x-ui.avatar name="John Doe" size="md" />
    <div>
        <p class="text-sm font-medium text-gray-900">John Doe</p>
        <p class="text-xs text-gray-500">john.doe@example.com</p>
    </div>
</div>
```

## Testing

To test all feedback components, visit:
```
http://your-app-url/demo/feedback
```

This page demonstrates all variants and usage examples for the three components.

## Requirements Satisfied

- ✅ 1.2: Components follow consistent design system with variants
- ✅ 1.3: Components use pure Tailwind utility classes
- ✅ 10.3: Spinner component for loading states with size and color variants
- ✅ 10.4: Skeleton component for loading placeholders with type variants
- ✅ Avatar component with image and initials fallback
