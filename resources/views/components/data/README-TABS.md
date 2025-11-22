# Tabs Component Documentation

## Overview

The Tabs component provides a tabbed interface for organizing content into separate panels. It uses Alpine.js for reactive tab switching with smooth transitions and supports icons, badges, and customizable styling.

## Components

### 1. `<x-data.tabs>` - Container Component
The main wrapper component that manages tab state.

### 2. `<x-data.tab>` - Dual-Purpose Component
Can be used as both a tab button (in the `tabs` slot) and a tab panel (with `panel` attribute).

## Props

### Tabs Container Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `defaultTab` | integer | `0` | Index of the tab that should be active by default |

### Tab Component Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `name` | string | `''` | Label text for the tab button |
| `index` | integer | `0` | Unique index for the tab (must match between button and panel) |
| `icon` | string | `null` | Optional icon name (Heroicon) to display before the label |
| `badge` | string/number | `null` | Optional badge content (typically a count) |
| `panel` | boolean | `false` | When present, renders as a panel instead of a button |

## Usage Examples

### Basic Tabs

```blade
<x-data.tabs>
    <x-slot:tabs>
        <x-data.tab name="Profile" :index="0" />
        <x-data.tab name="Settings" :index="1" />
        <x-data.tab name="Notifications" :index="2" />
    </x-slot:tabs>

    <x-data.tab :index="0" panel>
        <p>Profile content goes here</p>
    </x-data.tab>

    <x-data.tab :index="1" panel>
        <p>Settings content goes here</p>
    </x-data.tab>

    <x-data.tab :index="2" panel>
        <p>Notifications content goes here</p>
    </x-data.tab>
</x-data.tabs>
```

### Tabs with Icons

```blade
<x-data.tabs>
    <x-slot:tabs>
        <x-data.tab name="Dashboard" :index="0" icon="chart-bar" />
        <x-data.tab name="Users" :index="1" icon="users" />
        <x-data.tab name="Reports" :index="2" icon="document-text" />
    </x-slot:tabs>

    <x-data.tab :index="0" panel>
        <p>Dashboard content</p>
    </x-data.tab>

    <x-data.tab :index="1" panel>
        <p>Users content</p>
    </x-data.tab>

    <x-data.tab :index="2" panel>
        <p>Reports content</p>
    </x-data.tab>
</x-data.tabs>
```

### Tabs with Badges

```blade
<x-data.tabs>
    <x-slot:tabs>
        <x-data.tab name="All" :index="0" badge="24" />
        <x-data.tab name="Unread" :index="1" badge="5" />
        <x-data.tab name="Archived" :index="2" badge="19" />
    </x-slot:tabs>

    <x-data.tab :index="0" panel>
        <p>All messages (24)</p>
    </x-data.tab>

    <x-data.tab :index="1" panel>
        <p>Unread messages (5)</p>
    </x-data.tab>

    <x-data.tab :index="2" panel>
        <p>Archived messages (19)</p>
    </x-data.tab>
</x-data.tabs>
```

### Tabs with Icons and Badges

```blade
<x-data.tabs>
    <x-slot:tabs>
        <x-data.tab name="Inbox" :index="0" icon="inbox" badge="12" />
        <x-data.tab name="Important" :index="1" icon="star" badge="3" />
        <x-data.tab name="Sent" :index="2" icon="paper-airplane" />
    </x-slot:tabs>

    <x-data.tab :index="0" panel>
        <p>Inbox content</p>
    </x-data.tab>

    <x-data.tab :index="1" panel>
        <p>Important content</p>
    </x-data.tab>

    <x-data.tab :index="2" panel>
        <p>Sent content</p>
    </x-data.tab>
</x-data.tabs>
```

### Setting Default Active Tab

```blade
<x-data.tabs :defaultTab="1">
    <x-slot:tabs>
        <x-data.tab name="Tab 1" :index="0" />
        <x-data.tab name="Tab 2" :index="1" />
        <x-data.tab name="Tab 3" :index="2" />
    </x-slot:tabs>

    <x-data.tab :index="0" panel>
        <p>Tab 1 content</p>
    </x-data.tab>

    <x-data.tab :index="1" panel>
        <p>Tab 2 content (active by default)</p>
    </x-data.tab>

    <x-data.tab :index="2" panel>
        <p>Tab 3 content</p>
    </x-data.tab>
</x-data.tabs>
```

### Complex Content (Forms, Cards, etc.)

```blade
<x-data.tabs>
    <x-slot:tabs>
        <x-data.tab name="Personal Info" :index="0" icon="user" />
        <x-data.tab name="Security" :index="1" icon="lock-closed" />
    </x-slot:tabs>

    <x-data.tab :index="0" panel>
        <div class="space-y-4">
            <x-ui.input 
                label="Full Name" 
                name="name" 
                placeholder="Enter your name" 
            />
            <x-ui.input 
                label="Email" 
                name="email" 
                type="email" 
                placeholder="Enter your email" 
            />
            <x-ui.button variant="primary">Save Changes</x-ui.button>
        </div>
    </x-data.tab>

    <x-data.tab :index="1" panel>
        <div class="space-y-4">
            <x-ui.input 
                label="Current Password" 
                name="current_password" 
                type="password" 
            />
            <x-ui.input 
                label="New Password" 
                name="new_password" 
                type="password" 
            />
            <x-ui.button variant="primary">Update Password</x-ui.button>
        </div>
    </x-data.tab>
</x-data.tabs>
```

## Features

### 1. Alpine.js Integration
- Uses Alpine.js `x-data` for reactive state management
- Smooth tab switching without page reload
- Maintains active tab state

### 2. Active State Styling
- Active tab has primary color border and text
- Inactive tabs have gray color with hover effects
- Clear visual indication of current tab

### 3. Smooth Transitions
- Fade and slide animations when switching panels
- 200ms enter transition, 150ms leave transition
- Smooth opacity and transform changes

### 4. Icon Support
- Optional icons before tab labels
- Icons change color based on active state
- Uses the `<x-ui.icon>` component

### 5. Badge Support
- Display counts or notifications on tabs
- Badges change color based on active state
- Rounded pill shape for visual appeal

### 6. Accessibility
- Proper ARIA attributes (`aria-selected`, `role="tab"`, `role="tabpanel"`)
- Keyboard focus states with ring
- Semantic HTML structure

### 7. Responsive Design
- Tabs wrap on smaller screens
- Maintains spacing and alignment
- Touch-friendly tap targets

## Styling

### Active Tab
- Border: `border-primary-500` (bottom border)
- Text: `text-primary-600`
- Icon: `text-primary-500`
- Badge: `bg-primary-100 text-primary-600`

### Inactive Tab
- Border: `border-transparent`
- Text: `text-gray-500`
- Hover Text: `text-gray-700`
- Hover Border: `border-gray-300`
- Icon: `text-gray-400`
- Badge: `bg-gray-100 text-gray-600`

### Transitions
- Tab buttons: `transition-colors duration-200`
- Tab panels: Enter/leave transitions with opacity and transform
- Smooth color changes on hover and active state

## Best Practices

1. **Consistent Indexing**: Ensure tab buttons and panels use matching index values
2. **Meaningful Labels**: Use clear, concise tab names
3. **Icon Selection**: Choose icons that represent the tab content
4. **Badge Usage**: Use badges for counts or notifications, not long text
5. **Content Organization**: Group related content in the same tab
6. **Default Tab**: Set `defaultTab` to the most commonly used tab
7. **Accessibility**: Ensure tab content is keyboard navigable

## Common Patterns

### Dashboard Tabs
```blade
<x-data.tabs>
    <x-slot:tabs>
        <x-data.tab name="Overview" :index="0" icon="chart-bar" />
        <x-data.tab name="Analytics" :index="1" icon="chart-pie" />
        <x-data.tab name="Reports" :index="2" icon="document-text" />
    </x-slot:tabs>
    <!-- Panels with stats, charts, etc. -->
</x-data.tabs>
```

### Settings Tabs
```blade
<x-data.tabs>
    <x-slot:tabs>
        <x-data.tab name="General" :index="0" icon="cog" />
        <x-data.tab name="Security" :index="1" icon="lock-closed" />
        <x-data.tab name="Notifications" :index="2" icon="bell" />
    </x-slot:tabs>
    <!-- Panels with form sections -->
</x-data.tabs>
```

### Message Tabs
```blade
<x-data.tabs>
    <x-slot:tabs>
        <x-data.tab name="Inbox" :index="0" icon="inbox" badge="12" />
        <x-data.tab name="Sent" :index="1" icon="paper-airplane" />
        <x-data.tab name="Drafts" :index="2" icon="document" badge="3" />
    </x-slot:tabs>
    <!-- Panels with message lists -->
</x-data.tabs>
```

## Testing

To test the component, create a route to the test file:

```php
// routes/web.php
Route::get('/test/tabs', function () {
    return view('components.data.tabs-test');
});
```

Then visit `/test/tabs` in your browser to see all variations.

## Requirements Met

- ✅ **Requirement 1.2**: Component follows design system with consistent variants
- ✅ **Requirement 1.3**: Uses pure Tailwind utility classes
- ✅ Alpine.js integration for tab switching
- ✅ Active state styling with clear visual indication
- ✅ Smooth transitions between panels
- ✅ Support for icons and badges
- ✅ Accessibility features (ARIA attributes)
- ✅ Responsive design
- ✅ Tested with multiple tab panels

## Browser Compatibility

- Chrome/Edge: ✅ Full support
- Firefox: ✅ Full support
- Safari: ✅ Full support
- Mobile browsers: ✅ Full support

## Dependencies

- Alpine.js (for reactive state management)
- Tailwind CSS (for styling)
- `<x-ui.icon>` component (for icons)
