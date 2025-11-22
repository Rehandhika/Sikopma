# Form Components Documentation

This document provides comprehensive documentation for all form components in the SIKOPMA design system.

## Components Overview

- **Select**: Dropdown selection component
- **Textarea**: Multi-line text input component
- **Checkbox**: Single checkbox or checkbox group component
- **Radio**: Radio button component for mutually exclusive options

All form components follow consistent design patterns and support:
- Labels with required indicators
- Error states with validation messages
- Help text
- Disabled states
- Livewire wire:model integration

---

## Select Component

### Basic Usage

```blade
<x-ui.select
    name="country"
    label="Country"
    :options="[
        'id' => 'Indonesia',
        'my' => 'Malaysia',
        'sg' => 'Singapore',
    ]"
/>
```

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | string | null | Label text displayed above the select |
| `name` | string | '' | Name attribute for the select element |
| `placeholder` | string | 'Pilih opsi...' | Placeholder text for empty option |
| `required` | boolean | false | Shows asterisk (*) indicator |
| `disabled` | boolean | false | Disables the select |
| `error` | string | null | Error message to display |
| `help` | string | null | Help text displayed below select |
| `options` | array | [] | Array of options (value => label) |
| `selected` | string | null | Pre-selected value |

### Examples

**With Required Indicator:**
```blade
<x-ui.select
    name="role"
    label="Role"
    required
    :options="[
        'admin' => 'Administrator',
        'user' => 'User',
    ]"
/>
```

**With Error State:**
```blade
<x-ui.select
    name="category"
    label="Category"
    :options="$categories"
    error="Please select a valid category"
/>
```

**With Livewire:**
```blade
<x-ui.select
    name="status"
    label="Status"
    wire:model.live="status"
    :options="[
        'active' => 'Active',
        'inactive' => 'Inactive',
    ]"
    :error="$errors->first('status')"
/>
```

**Disabled State:**
```blade
<x-ui.select
    name="locked_field"
    label="Locked Field"
    disabled
    :options="$options"
    selected="default"
/>
```

---

## Textarea Component

### Basic Usage

```blade
<x-ui.textarea
    name="description"
    label="Description"
    placeholder="Enter description..."
/>
```

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | string | null | Label text displayed above the textarea |
| `name` | string | '' | Name attribute for the textarea element |
| `placeholder` | string | '' | Placeholder text |
| `required` | boolean | false | Shows asterisk (*) indicator |
| `disabled` | boolean | false | Disables the textarea |
| `error` | string | null | Error message to display |
| `help` | string | null | Help text displayed below textarea |
| `rows` | integer | 3 | Number of visible text rows |

### Examples

**With Default Content:**
```blade
<x-ui.textarea
    name="notes"
    label="Notes"
    rows="5"
>Default content here</x-ui.textarea>
```

**With Character Limit Help:**
```blade
<x-ui.textarea
    name="bio"
    label="Bio"
    rows="4"
    help="Maximum 500 characters"
    placeholder="Tell us about yourself..."
/>
```

**With Error State:**
```blade
<x-ui.textarea
    name="feedback"
    label="Feedback"
    error="Feedback must be at least 10 characters"
/>
```

**With Livewire:**
```blade
<x-ui.textarea
    name="comments"
    label="Comments"
    wire:model.live="comments"
    rows="4"
    :error="$errors->first('comments')"
/>
```

---

## Checkbox Component

### Basic Usage

```blade
<x-ui.checkbox
    name="terms"
    label="I agree to the terms and conditions"
/>
```

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | string | null | Label text displayed next to checkbox |
| `name` | string | '' | Name attribute for the checkbox element |
| `value` | string | '1' | Value when checkbox is checked |
| `checked` | boolean | false | Pre-checked state |
| `disabled` | boolean | false | Disables the checkbox |
| `error` | string | null | Error message to display |
| `help` | string | null | Help text displayed below checkbox |
| `description` | string | null | Additional description text |

### Examples

**With Description:**
```blade
<x-ui.checkbox
    name="newsletter"
    label="Subscribe to newsletter"
    description="Get weekly updates about new features and products"
/>
```

**Pre-checked:**
```blade
<x-ui.checkbox
    name="notifications"
    label="Enable notifications"
    checked
/>
```

**With Error State:**
```blade
<x-ui.checkbox
    name="accept_terms"
    label="Accept terms and conditions"
    error="You must accept the terms to continue"
/>
```

**With Livewire:**
```blade
<x-ui.checkbox
    name="email_notifications"
    label="Email notifications"
    wire:model="email_notifications"
    :error="$errors->first('email_notifications')"
/>
```

**Checkbox Group:**
```blade
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Preferences
    </label>
    <div class="space-y-2">
        <x-ui.checkbox
            name="pref_email"
            label="Email notifications"
            wire:model="preferences.email"
        />
        
        <x-ui.checkbox
            name="pref_sms"
            label="SMS notifications"
            wire:model="preferences.sms"
        />
        
        <x-ui.checkbox
            name="pref_push"
            label="Push notifications"
            wire:model="preferences.push"
        />
    </div>
</div>
```

---

## Radio Component

### Basic Usage

```blade
<x-ui.radio
    name="payment"
    value="credit_card"
    label="Credit Card"
/>
```

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | string | null | Label text displayed next to radio |
| `name` | string | '' | Name attribute (same for all radios in group) |
| `value` | string | '' | Value for this radio option |
| `checked` | boolean | false | Pre-checked state |
| `disabled` | boolean | false | Disables the radio |
| `error` | string | null | Error message to display |
| `help` | string | null | Help text displayed below radio |
| `description` | string | null | Additional description text |

### Examples

**Radio Group:**
```blade
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Payment Method
    </label>
    <div class="space-y-2">
        <x-ui.radio
            name="payment"
            value="credit_card"
            label="Credit Card"
            description="Pay with Visa, Mastercard, or American Express"
            checked
        />
        
        <x-ui.radio
            name="payment"
            value="bank_transfer"
            label="Bank Transfer"
            description="Direct transfer to our bank account"
        />
        
        <x-ui.radio
            name="payment"
            value="ewallet"
            label="E-Wallet"
            description="Pay with GoPay, OVO, or Dana"
        />
    </div>
</div>
```

**With Error State:**
```blade
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Shipping Method
    </label>
    <div class="space-y-2">
        <x-ui.radio
            name="shipping"
            value="standard"
            label="Standard Shipping"
            error="Please select a shipping method"
        />
        
        <x-ui.radio
            name="shipping"
            value="express"
            label="Express Shipping"
        />
    </div>
</div>
```

**With Livewire:**
```blade
<div>
    <label class="block text-sm font-medium text-gray-700 mb-2">
        Account Type
    </label>
    <div class="space-y-2">
        <x-ui.radio
            name="account_type"
            value="personal"
            label="Personal"
            wire:model="account_type"
        />
        
        <x-ui.radio
            name="account_type"
            value="business"
            label="Business"
            wire:model="account_type"
        />
    </div>
</div>
```

---

## Complete Form Example

```blade
<form wire:submit.prevent="submit" class="space-y-6">
    <!-- Select -->
    <x-ui.select
        name="country"
        label="Country"
        wire:model.live="country"
        :options="$countries"
        required
        :error="$errors->first('country')"
    />
    
    <!-- Textarea -->
    <x-ui.textarea
        name="description"
        label="Description"
        wire:model.live="description"
        rows="4"
        help="Maximum 500 characters"
        :error="$errors->first('description')"
    />
    
    <!-- Checkbox Group -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Preferences
        </label>
        <div class="space-y-2">
            <x-ui.checkbox
                name="email_notifications"
                label="Email notifications"
                wire:model="email_notifications"
            />
            
            <x-ui.checkbox
                name="sms_notifications"
                label="SMS notifications"
                wire:model="sms_notifications"
            />
        </div>
    </div>
    
    <!-- Radio Group -->
    <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">
            Account Type
        </label>
        <div class="space-y-2">
            <x-ui.radio
                name="account_type"
                value="personal"
                label="Personal"
                wire:model="account_type"
            />
            
            <x-ui.radio
                name="account_type"
                value="business"
                label="Business"
                wire:model="account_type"
            />
        </div>
    </div>
    
    <!-- Submit Button -->
    <div class="flex justify-end">
        <x-ui.button variant="primary" type="submit">
            Submit
        </x-ui.button>
    </div>
</form>
```

---

## Styling Consistency

All form components follow these design principles:

### Focus States
- Primary color ring on focus
- Smooth transition (200ms)
- 2px ring width with offset

### Error States
- Red border color (`border-danger-300`)
- Red text color for error messages
- Error icon displayed with message
- Red focus ring (`focus:ring-danger-500`)

### Disabled States
- Gray background (`bg-gray-50`)
- Gray text color (`text-gray-500`)
- Not-allowed cursor
- Reduced opacity

### Spacing
- Consistent padding: `px-3 py-2`
- Label margin: `mb-1`
- Help text size: `text-xs`
- Error message size: `text-xs`

### Typography
- Label: `text-sm font-medium text-gray-700`
- Input text: `text-sm`
- Help text: `text-xs text-gray-500`
- Error text: `text-xs text-danger-600`

---

## Testing

### Demo Pages

1. **Static Demo**: Visit `/demo/form-components` to see all form components with various states
2. **Livewire Validation Demo**: Visit `/demo/form-validation` to test form validation integration

### Manual Testing Checklist

- [ ] All components render correctly
- [ ] Labels display with required indicators
- [ ] Error states show red borders and error messages
- [ ] Help text displays correctly
- [ ] Disabled states work properly
- [ ] Focus states show primary color ring
- [ ] Livewire wire:model binding works
- [ ] Form validation displays errors correctly
- [ ] Keyboard navigation works (Tab, Space, Enter)
- [ ] Responsive behavior on mobile devices

---

## Accessibility

All form components follow accessibility best practices:

- Semantic HTML elements (`<select>`, `<textarea>`, `<input type="checkbox">`, `<input type="radio">`)
- Proper `<label>` associations using `for` and `id` attributes
- Required indicators for screen readers
- Error messages associated with form fields
- Keyboard navigation support
- Focus indicators visible and clear
- Sufficient color contrast (WCAG AA compliant)

---

## Browser Support

- Chrome (latest)
- Firefox (latest)
- Safari (latest)
- Edge (latest)

---

## Related Components

- [Button Component](./README-BUTTON.md)
- [Input Component](./README-INPUT.md)
- Icon Component (for error icons)
- Alert Component (for form-level messages)

---

## Requirements Fulfilled

This implementation satisfies the following requirements:

- **1.2**: Component library with consistent variants
- **1.3**: Consistent styling across all components
- **8.1**: Error states with red color and error icons
- **8.2**: Required field indicators with asterisk
- **8.3**: Focus states with brand color ring
- **8.4**: Comprehensive form components (select, textarea, checkbox, radio)
- **8.5**: Disabled states with reduced opacity

---

## Notes

- All components use Tailwind utility classes only (no custom CSS)
- Components are fully compatible with Livewire wire:model
- Error messages automatically hide help text when displayed
- Radio buttons use unique IDs combining name and value
- Textarea supports resize-y by default
- Select component supports both associative and indexed arrays for options
