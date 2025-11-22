# Stat Card Component

## Overview
The stat card component is designed to display key metrics and statistics in a visually appealing card format. It's perfect for dashboard metrics, KPIs, and data visualization.

## Location
`resources/views/components/layout/stat-card.blade.php`

## Features
- ✅ Label, value, and subtitle display
- ✅ Optional icon with customizable colors
- ✅ Trend indicator with up/down arrows
- ✅ Hover effect with smooth shadow transition
- ✅ Responsive design
- ✅ Flexible grid layout support
- ✅ Multiple color variants

## Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| `label` | string | `''` | The label/title of the metric |
| `value` | string/number | `''` | The main value to display |
| `icon` | string | `null` | Icon name from the icon component |
| `iconColor` | string | `'bg-primary-100'` | Background color class for icon container |
| `iconTextColor` | string | `'text-primary-600'` | Text color class for icon |
| `trend` | string | `null` | Trend indicator (e.g., "+12.5%", "-8.3%") |
| `trendUp` | boolean | `true` | Whether trend is positive (green) or negative (red) |
| `subtitle` | string | `null` | Additional descriptive text below the value |

## Usage Examples

### Basic Stat Card
```blade
<x-layout.stat-card
    label="Total Users"
    value="1,234"
/>
```

### With Icon
```blade
<x-layout.stat-card
    label="Total Sales"
    value="Rp 45.2M"
    icon="currency-dollar"
    iconColor="bg-success-100"
    iconTextColor="text-success-600"
/>
```

### With Trend Indicator
```blade
<x-layout.stat-card
    label="Revenue"
    value="Rp 125.5M"
    icon="chart-bar"
    iconColor="bg-success-100"
    iconTextColor="text-success-600"
    trend="+12.5%"
    :trendUp="true"
/>
```

### With Subtitle
```blade
<x-layout.stat-card
    label="Attendance Rate"
    value="94.5%"
    subtitle="Last 30 days average"
    icon="check-circle"
    iconColor="bg-success-100"
    iconTextColor="text-success-600"
/>
```

### Complete Example (All Features)
```blade
<x-layout.stat-card
    label="Monthly Revenue"
    value="Rp 156.8M"
    subtitle="Compared to last month"
    icon="currency-dollar"
    iconColor="bg-success-100"
    iconTextColor="text-success-600"
    trend="+23.1%"
    :trendUp="true"
/>
```

### In Grid Layout
```blade
<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
    <x-layout.stat-card
        label="Total Users"
        value="1,234"
        icon="users"
        iconColor="bg-primary-100"
        iconTextColor="text-primary-600"
    />

    <x-layout.stat-card
        label="Total Sales"
        value="Rp 45.2M"
        icon="currency-dollar"
        iconColor="bg-success-100"
        iconTextColor="text-success-600"
    />

    <x-layout.stat-card
        label="Active Sessions"
        value="892"
        icon="clock"
        iconColor="bg-info-100"
        iconTextColor="text-info-600"
    />

    <x-layout.stat-card
        label="Pending Tasks"
        value="23"
        icon="inbox"
        iconColor="bg-warning-100"
        iconTextColor="text-warning-600"
    />
</div>
```

## Color Variants

### Primary
```blade
<x-layout.stat-card
    label="Metric"
    value="1,234"
    icon="chart-bar"
    iconColor="bg-primary-100"
    iconTextColor="text-primary-600"
/>
```

### Secondary
```blade
<x-layout.stat-card
    label="Metric"
    value="5,678"
    icon="users"
    iconColor="bg-secondary-100"
    iconTextColor="text-secondary-600"
/>
```

### Success
```blade
<x-layout.stat-card
    label="Metric"
    value="9,012"
    icon="check-circle"
    iconColor="bg-success-100"
    iconTextColor="text-success-600"
/>
```

### Danger
```blade
<x-layout.stat-card
    label="Metric"
    value="345"
    icon="x-circle"
    iconColor="bg-danger-100"
    iconTextColor="text-danger-600"
/>
```

### Warning
```blade
<x-layout.stat-card
    label="Metric"
    value="678"
    icon="exclamation-triangle"
    iconColor="bg-warning-100"
    iconTextColor="text-warning-600"
/>
```

### Info
```blade
<x-layout.stat-card
    label="Metric"
    value="901"
    icon="information-circle"
    iconColor="bg-info-100"
    iconTextColor="text-info-600"
/>
```

## Responsive Behavior

The stat card is designed to work seamlessly in responsive grid layouts:

```blade
<!-- Mobile: 1 column, Tablet: 2 columns, Desktop: 3 columns, XL: 4 columns -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
    <!-- Stat cards here -->
</div>
```

## Styling Details

### Hover Effect
- Default shadow: `shadow-md`
- Hover shadow: `shadow-lg`
- Transition: `duration-200`

### Typography
- Label: `text-sm font-medium text-gray-500`
- Value: `text-2xl font-semibold text-gray-900`
- Subtitle: `text-xs text-gray-500`
- Trend: `text-sm font-medium` (green for up, red for down)

### Spacing
- Card padding: `p-5`
- Icon container padding: `p-3`
- Icon size: `w-6 h-6`
- Margin between icon and content: `ml-5`

## Accessibility

- Uses semantic HTML (`<dt>` and `<dd>` tags for definition lists)
- Proper color contrast ratios
- Icon provides visual reinforcement
- Text remains readable without icons

## Testing

View the test page at: `/demo/stat-card`

The test page includes:
1. Basic stat cards with icons
2. Stat cards with trend indicators
3. Stat cards with subtitles
4. Stat cards with all features
5. Stat cards without icons
6. Different color variants
7. Responsive grid layout tests
8. Hover effect demonstrations
9. Long text handling

## Requirements Met

This component satisfies the following requirements:
- **1.2**: Component with consistent variants and props
- **1.3**: Uses pure Tailwind utility classes
- **10.5**: Stat card for displaying metrics with icon, label, value, and trend
- **11.1**: Responsive design for mobile devices
- **11.2**: Responsive design for tablet and desktop

## Integration Example (Dashboard)

```blade
<!-- In your dashboard view -->
<div class="mb-8">
    <h2 class="text-xl font-semibold text-gray-900 mb-4">Overview</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <x-layout.stat-card
            label="Total Members"
            value="{{ number_format($totalMembers) }}"
            subtitle="Active members"
            icon="users"
            iconColor="bg-primary-100"
            iconTextColor="text-primary-600"
            trend="{{ $membersTrend }}"
            :trendUp="$membersTrendUp"
        />

        <x-layout.stat-card
            label="Today's Sales"
            value="Rp {{ number_format($todaySales) }}"
            subtitle="Compared to yesterday"
            icon="currency-dollar"
            iconColor="bg-success-100"
            iconTextColor="text-success-600"
            trend="{{ $salesTrend }}"
            :trendUp="$salesTrendUp"
        />

        <x-layout.stat-card
            label="Attendance Rate"
            value="{{ $attendanceRate }}%"
            subtitle="This month"
            icon="check-circle"
            iconColor="bg-info-100"
            iconTextColor="text-info-600"
            trend="{{ $attendanceTrend }}"
            :trendUp="$attendanceTrendUp"
        />

        <x-layout.stat-card
            label="Pending Tasks"
            value="{{ $pendingTasks }}"
            subtitle="Requires attention"
            icon="inbox"
            iconColor="bg-warning-100"
            iconTextColor="text-warning-600"
        />
    </div>
</div>
```

## Notes

- The component uses flexbox for layout, ensuring proper alignment
- Icon is optional and the layout adjusts accordingly
- Trend indicator automatically shows up/down arrows based on `trendUp` prop
- All color classes can be customized via props
- Component is fully compatible with Livewire reactive properties
