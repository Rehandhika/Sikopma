# Input Component

A comprehensive input component with validation states, icons, help text, and full Livewire compatibility.

## Location
`resources/views/components/ui/input.blade.php`

## Features

✅ Label with required indicator  
✅ Multiple input types (text, email, password, number, tel, url, date, etc.)  
✅ Placeholder support  
✅ Error state with error message display  
✅ Help text support  
✅ Leading icon support  
✅ Disabled state  
✅ Full Livewire wire:model compatibility  
✅ Consistent styling with design system  
✅ Focus states with primary color ring  
✅ Smooth transitions  

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | string\|null | null | Label text displayed above input |
| `name` | string | '' | Input name attribute (required) |
| `type` | string | 'text' | Input type (text, email, password, number, tel, url, date, etc.) |
| `placeholder` | string | '' | Placeholder text |
| `required` | boolean | false | Shows red asterisk (*) next to label |
| `disabled` | boolean | false | Disables the input with visual feedback |
| `error` | string\|null | null | Error message to display (also changes input styling) |
| `help` | string\|null | null | Help text displayed below input (hidden when error is shown) |
| `icon` | string\|null | null | Leading icon name (from icon component) |

## Usage Examples

### Basic Input
```blade
<x-ui.input 
    label="Email Address"
    name="email"
    type="email"
    placeholder="Enter your email"
/>
```

### Required Input
```blade
<x-ui.input 
    label="Username"
    name="username"
    type="text"
    placeholder="Enter username"
    :required="true"
/>
```

### Input with Help Text
```blade
<x-ui.input 
    label="Password"
    name="password"
    type="password"
    placeholder="Enter password"
    help="Password must be at least 8 characters long"
/>
```

### Input with Error State
```blade
<x-ui.input 
    label="Phone Number"
    name="phone"
    type="tel"
    placeholder="Enter phone number"
    error="Phone number is required"
/>
```

### Input with Leading Icon
```blade
<x-ui.input 
    label="Search"
    name="search"
    type="text"
    placeholder="Search..."
    icon="magnifying-glass"
/>
```

### Input with Icon and Error
```blade
<x-ui.input 
    label="Email"
    name="email"
    type="email"
    placeholder="Enter email"
    icon="user"
    error="Invalid email format"
/>
```

### Disabled Input
```blade
<x-ui.input 
    label="Disabled Field"
    name="disabled"
    type="text"
    placeholder="This field is disabled"
    :disabled="true"
    value="Cannot edit this"
/>
```

### Livewire Integration
```blade
<x-ui.input 
    label="Username"
    name="username"
    type="text"
    placeholder="Enter username"
    wire:model="username"
    :error="$errors->first('username')"
/>
```

### Livewire with Live Validation
```blade
<x-ui.input 
    label="Email"
    name="email"
    type="email"
    placeholder="Enter email"
    wire:model.live="email"
    :error="$errors->first('email')"
    help="We'll never share your email"
/>
```

### All Features Combined
```blade
<x-ui.input 
    label="Full Featured Input"
    name="full_featured"
    type="text"
    placeholder="Enter value"
    icon="user"
    :required="true"
    help="This input has all features enabled"
    wire:model="fullFeatured"
/>
```

## Styling Details

### Normal State
- Border: `border-gray-300`
- Focus: `focus:border-primary-500 focus:ring-primary-500`
- Padding: `px-3 py-2` (or `pl-10` when icon is present)
- Border radius: `rounded-lg`
- Shadow: `shadow-sm`

### Error State
- Border: `border-danger-300`
- Text: `text-danger-900`
- Placeholder: `placeholder-danger-300`
- Focus: `focus:border-danger-500 focus:ring-danger-500`
- Error message with icon displayed below input

### Disabled State
- Background: `bg-gray-50`
- Text: `text-gray-500`
- Cursor: `cursor-not-allowed`
- Opacity: `opacity-50` (via disabled attribute)

### Transitions
- All color changes: `transition-colors duration-200`
- Focus ring: `focus:ring-2 focus:ring-offset-0`

## Available Icons

Common icons you can use with the `icon` prop:
- `user` - User profile
- `magnifying-glass` - Search
- `envelope` - Email
- `lock-closed` - Password
- `phone` - Phone number
- `calendar` - Date
- `link` - URL
- `currency-dollar` - Money/price
- `home` - Address
- `building-office` - Company

See `resources/views/components/ui/icon.blade.php` for full list.

## Form Integration

### Standard Form
```blade
<form method="POST" action="{{ route('users.store') }}">
    @csrf
    
    <x-ui.input 
        label="Name"
        name="name"
        type="text"
        :required="true"
        :error="$errors->first('name')"
        value="{{ old('name') }}"
    />
    
    <x-ui.input 
        label="Email"
        name="email"
        type="email"
        :required="true"
        :error="$errors->first('email')"
        value="{{ old('email') }}"
    />
    
    <button type="submit">Submit</button>
</form>
```

### Livewire Form
```blade
<form wire:submit="save">
    <x-ui.input 
        label="Name"
        name="name"
        type="text"
        :required="true"
        wire:model="name"
        :error="$errors->first('name')"
    />
    
    <x-ui.input 
        label="Email"
        name="email"
        type="email"
        :required="true"
        wire:model="email"
        :error="$errors->first('email')"
    />
    
    <button type="submit">Save</button>
</form>
```

## Accessibility

- Proper `<label>` with `for` attribute linked to input `id`
- Required indicator visually distinct with red asterisk
- Error messages associated with input
- Disabled state properly communicated
- Focus states clearly visible
- Color contrast meets WCAG AA standards

## Testing

Visit `/demo/input` to see all input variations and test functionality.

## Requirements Satisfied

This component satisfies the following requirements from the spec:
- **1.2**: Component with consistent variants
- **1.3**: Uses pure Tailwind utility classes
- **2.4**: Replaces custom .input class
- **3.1**: Uses only Tailwind utilities
- **3.2**: Consistent prop naming (error, disabled, required)
- **8.1**: Error state with red color and error icon
- **8.2**: Required indicator with asterisk
- **8.3**: Focus ring with brand color
- **8.5**: Disabled state with reduced opacity

## Notes

- The component uses `space-y-1` for consistent spacing between label, input, and help/error text
- Error messages take precedence over help text (help text is hidden when error is present)
- The component merges additional attributes, so you can add custom classes or attributes as needed
- Icon adds left padding automatically (`pl-10`) to accommodate the icon space
- All color classes use the design system tokens (primary, danger, gray)
