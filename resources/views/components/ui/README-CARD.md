# Card Component

A flexible card component with header, footer slots, and customizable styling options.

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | string\|null | `null` | Optional title displayed in the card header |
| `subtitle` | string\|null | `null` | Optional subtitle displayed below the title |
| `padding` | boolean | `true` | Whether to apply padding to the content area |
| `shadow` | string | `'md'` | Shadow variant: `'none'`, `'sm'`, `'md'`, `'lg'` |

## Slots

| Slot | Description |
|------|-------------|
| Default slot | Main content area of the card |
| `footer` | Optional footer section with gray background |

## Usage Examples

### Basic Card
```blade
<x-ui.card>
    <p>This is a basic card with default padding and shadow.</p>
</x-ui.card>
```

### Card with Title
```blade
<x-ui.card title="Card Title">
    <p>Card content goes here.</p>
</x-ui.card>
```

### Card with Title and Subtitle
```blade
<x-ui.card title="User Profile" subtitle="Manage your personal information">
    <p>Profile content here.</p>
</x-ui.card>
```

### Card with Footer
```blade
<x-ui.card title="Confirm Action">
    <p>Are you sure you want to continue?</p>
    
    <x-slot:footer>
        <div class="flex justify-end space-x-3">
            <x-ui.button variant="white">Cancel</x-ui.button>
            <x-ui.button variant="primary">Confirm</x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.card>
```

### Shadow Variants
```blade
<x-ui.card shadow="none">No shadow</x-ui.card>
<x-ui.card shadow="sm">Small shadow</x-ui.card>
<x-ui.card shadow="md">Medium shadow (default)</x-ui.card>
<x-ui.card shadow="lg">Large shadow</x-ui.card>
```

### Card without Padding
```blade
<x-ui.card :padding="false">
    <img src="image.jpg" alt="Full width image" class="w-full">
    <div class="px-6 py-4">
        <p>Content with custom padding</p>
    </div>
</x-ui.card>
```

### Card with Form Components
```blade
<x-ui.card title="Login Form" subtitle="Enter your credentials">
    <form class="space-y-4">
        <x-ui.input label="Email" name="email" type="email" required />
        <x-ui.input label="Password" name="password" type="password" required />
        <x-ui.checkbox name="remember" label="Remember me" />
    </form>
    
    <x-slot:footer>
        <div class="flex justify-end">
            <x-ui.button variant="primary" type="submit">Sign In</x-ui.button>
        </div>
    </x-slot:footer>
</x-ui.card>
```

### Card with Custom Classes
```blade
<x-ui.card title="Custom Card" class="border-2 border-primary-500">
    <p>This card has custom border styling.</p>
</x-ui.card>
```

### Nested Cards
```blade
<x-ui.card title="Parent Card" shadow="lg">
    <div class="grid grid-cols-2 gap-4">
        <x-ui.card title="Child 1" shadow="sm">
            <p>Nested content</p>
        </x-ui.card>
        <x-ui.card title="Child 2" shadow="sm">
            <p>Nested content</p>
        </x-ui.card>
    </div>
</x-ui.card>
```

## Composition Examples

### Card Grid Layout
```blade
<div class="grid grid-cols-1 md:grid-cols-3 gap-6">
    <x-ui.card title="Feature 1" subtitle="Description">
        <p>Feature details</p>
        <x-slot:footer>
            <x-ui.button variant="outline" size="sm" class="w-full">Learn More</x-ui.button>
        </x-slot:footer>
    </x-ui.card>
    
    <x-ui.card title="Feature 2" subtitle="Description">
        <p>Feature details</p>
        <x-slot:footer>
            <x-ui.button variant="outline" size="sm" class="w-full">Learn More</x-ui.button>
        </x-slot:footer>
    </x-ui.card>
    
    <x-ui.card title="Feature 3" subtitle="Description">
        <p>Feature details</p>
        <x-slot:footer>
            <x-ui.button variant="outline" size="sm" class="w-full">Learn More</x-ui.button>
        </x-slot:footer>
    </x-ui.card>
</div>
```

## Design Specifications

- **Background**: White (`bg-white`)
- **Border**: Gray 200 (`border-gray-200`)
- **Border Radius**: Large (`rounded-lg`)
- **Header Background**: Gray 50 (`bg-gray-50`)
- **Footer Background**: Gray 50 (`bg-gray-50`)
- **Default Padding**: 1.5rem (24px) on all sides
- **Header/Footer Padding**: 1.5rem horizontal, 1rem vertical

## Accessibility

- Uses semantic HTML structure
- Header uses `<h3>` for title (can be customized if needed)
- Proper color contrast for text elements
- Supports custom ARIA attributes via the `$attributes` bag

## Demo

Visit `/demo/card` to see all card variations and compositions in action.

## Requirements Met

- ✅ 1.2: Component uses consistent Tailwind utility classes
- ✅ 1.3: Implements standard variant system (shadow variants)
- ✅ 3.1: Uses only Tailwind utility classes, no custom CSS
- ✅ 3.2: Consistent prop naming (title, subtitle, padding, shadow)
