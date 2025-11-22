# Form Section Component

## Overview

The `form-section` component provides a consistent layout structure for grouping form fields with optional title and description. It ensures consistent spacing between form elements and creates visual separation between different sections of a form.

## Location

`resources/views/components/layout/form-section.blade.php`

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `title` | string | `''` | Section heading text |
| `description` | string\|null | `null` | Optional description text below the title |

## Features

- **Consistent Spacing**: Automatically applies `space-y-4` (1rem/16px) between form fields
- **Visual Separation**: Title and description are separated from content with a bottom border
- **Flexible Layout**: Works with any form elements (inputs, selects, textareas, checkboxes, etc.)
- **Custom Classes**: Supports additional classes via `$attributes->merge()`
- **Optional Header**: Can be used without title for spacing-only functionality

## Usage Examples

### Basic Usage with Title and Description

```blade
<x-layout.form-section 
    title="Personal Information"
    description="Update your personal details and contact information."
>
    <x-ui.input 
        label="Full Name"
        name="full_name"
        required
    />
    
    <x-ui.input 
        label="Email Address"
        name="email"
        type="email"
        required
    />
    
    <x-ui.input 
        label="Phone Number"
        name="phone"
        type="tel"
    />
</x-layout.form-section>
```

### Title Only (No Description)

```blade
<x-layout.form-section title="Account Settings">
    <x-ui.input 
        label="Username"
        name="username"
        required
    />
    
    <x-ui.input 
        label="Password"
        name="password"
        type="password"
    />
</x-layout.form-section>
```

### Without Title (Spacing Only)

```blade
<x-layout.form-section>
    <x-ui.input label="Field 1" name="field1" />
    <x-ui.input label="Field 2" name="field2" />
    <x-ui.input label="Field 3" name="field3" />
</x-layout.form-section>
```

### Multiple Sections in One Form

```blade
<form class="space-y-8">
    <x-layout.form-section 
        title="Basic Information"
        description="Provide your basic details."
    >
        <x-ui.input label="First Name" name="first_name" required />
        <x-ui.input label="Last Name" name="last_name" required />
    </x-layout.form-section>

    <x-layout.form-section 
        title="Contact Details"
        description="How can we reach you?"
    >
        <x-ui.input label="Email" name="email" type="email" required />
        <x-ui.input label="Phone" name="phone" type="tel" />
    </x-layout.form-section>

    <x-layout.form-section 
        title="Preferences"
        description="Customize your experience."
    >
        <x-ui.checkbox label="Receive email notifications" name="email_notifications" />
        <x-ui.checkbox label="Receive SMS notifications" name="sms_notifications" />
    </x-layout.form-section>

    <div class="flex justify-end space-x-3">
        <x-ui.button variant="white" type="button">Cancel</x-ui.button>
        <x-ui.button variant="primary" type="submit">Save Changes</x-ui.button>
    </div>
</form>
```

### With Custom Classes

```blade
<x-layout.form-section 
    title="Custom Styled Section"
    description="This section has custom background and padding."
    class="bg-gray-50 p-6 rounded-lg"
>
    <x-ui.input label="Field 1" name="field1" />
    <x-ui.input label="Field 2" name="field2" />
</x-layout.form-section>
```

### With Grid Layout

```blade
<x-layout.form-section 
    title="Address Information"
    description="Enter your complete address."
>
    <x-ui.input 
        label="Street Address"
        name="address"
        placeholder="123 Main Street"
    />
    
    <div class="grid grid-cols-2 gap-4">
        <x-ui.input 
            label="City"
            name="city"
            placeholder="Jakarta"
        />
        
        <x-ui.input 
            label="Postal Code"
            name="postal_code"
            placeholder="12345"
        />
    </div>
    
    <x-ui.select 
        label="Country"
        name="country"
        :options="[
            '' => 'Select a country',
            'ID' => 'Indonesia',
            'MY' => 'Malaysia',
            'SG' => 'Singapore',
        ]"
    />
</x-layout.form-section>
```

### With Different Form Elements

```blade
<x-layout.form-section 
    title="Feedback Form"
    description="Share your thoughts with us."
>
    <x-ui.select 
        label="Category"
        name="category"
        :options="[
            '' => 'Select a category',
            'bug' => 'Bug Report',
            'feature' => 'Feature Request',
            'general' => 'General Feedback',
        ]"
        required
    />
    
    <x-ui.textarea 
        label="Message"
        name="message"
        placeholder="Tell us what you think..."
        rows="5"
        required
    />
    
    <x-ui.checkbox 
        label="I agree to the terms and conditions"
        name="agree_terms"
    />
</x-layout.form-section>
```

## Styling Details

### Container Classes
- `space-y-6`: Provides 1.5rem (24px) spacing between header and content

### Header Section (when title is provided)
- `border-b border-gray-200 pb-4`: Bottom border with padding for visual separation
- `text-lg font-medium text-gray-900`: Title styling
- `mt-1 text-sm text-gray-500`: Description styling

### Content Section
- `space-y-4`: Provides 1rem (16px) consistent spacing between form fields

## Design Principles

1. **Consistent Spacing**: All form fields within a section have uniform spacing
2. **Visual Hierarchy**: Clear separation between section header and content
3. **Flexibility**: Works with any form elements and layouts
4. **Accessibility**: Semantic HTML structure with proper heading hierarchy
5. **Customization**: Supports additional classes for specific use cases

## Best Practices

1. **Use for Grouping**: Group related form fields together
2. **Multiple Sections**: Use `space-y-8` on the parent form for separation between sections
3. **Title Hierarchy**: Use descriptive titles that clearly indicate the section purpose
4. **Description Usage**: Add descriptions when the section needs clarification
5. **Nested Layouts**: Combine with grid layouts for complex form structures

## Requirements Satisfied

- **Requirement 1.2**: Provides reusable component with consistent variants
- **Requirement 1.3**: Uses pure Tailwind utility classes
- **Requirement 6.2**: Implements form section component for grouping form fields with consistent spacing

## Testing

A comprehensive test file is available at `resources/views/components/layout/form-section-test.blade.php` that demonstrates:

1. Form section with title and description
2. Form section with title only
3. Form section without title (spacing only)
4. Multiple sections in one form
5. Custom classes application
6. Different form elements integration
7. Spacing consistency verification

## Related Components

- `x-ui.input` - Text input component
- `x-ui.select` - Select dropdown component
- `x-ui.textarea` - Textarea component
- `x-ui.checkbox` - Checkbox component
- `x-ui.radio` - Radio button component
- `x-ui.button` - Button component
- `x-layout.page-header` - Page header component

## Migration from Old Pattern

### Before (Hardcoded)
```blade
<div class="space-y-6">
    <div class="border-b border-gray-200 pb-4">
        <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>
        <p class="mt-1 text-sm text-gray-500">Update your details.</p>
    </div>
    <div class="space-y-4">
        <!-- form fields -->
    </div>
</div>
```

### After (Component)
```blade
<x-layout.form-section 
    title="Personal Information"
    description="Update your details."
>
    <!-- form fields -->
</x-layout.form-section>
```

## Notes

- The component uses `space-y-4` for form field spacing, which equals 1rem (16px)
- The outer container uses `space-y-6` for header-to-content spacing, which equals 1.5rem (24px)
- When using multiple sections, wrap them in a form with `space-y-8` for proper separation
- The component is fully compatible with Livewire `wire:model` bindings
