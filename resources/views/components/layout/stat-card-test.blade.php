<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stat Card Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Stat Card Component Test</h1>

        <!-- Test 1: Basic stat card with icon -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">1. Basic Stat Card with Icon</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
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

        <!-- Test 2: Stat cards with trend indicators -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">2. Stat Cards with Trend Indicators</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <x-layout.stat-card
                label="Revenue"
                value="Rp 125.5M"
                icon="chart-bar"
                iconColor="bg-success-100"
                iconTextColor="text-success-600"
                trend="+12.5%"
                :trendUp="true"
            />

            <x-layout.stat-card
                label="Expenses"
                value="Rp 45.2M"
                icon="currency-dollar"
                iconColor="bg-danger-100"
                iconTextColor="text-danger-600"
                trend="-8.3%"
                :trendUp="false"
            />

            <x-layout.stat-card
                label="Net Profit"
                value="Rp 80.3M"
                icon="chart-bar"
                iconColor="bg-primary-100"
                iconTextColor="text-primary-600"
                trend="+15.2%"
                :trendUp="true"
            />
        </div>

        <!-- Test 3: Stat cards with subtitles -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">3. Stat Cards with Subtitles</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <x-layout.stat-card
                label="Total Members"
                value="2,456"
                subtitle="Active members this month"
                icon="users"
                iconColor="bg-primary-100"
                iconTextColor="text-primary-600"
            />

            <x-layout.stat-card
                label="Attendance Rate"
                value="94.5%"
                subtitle="Last 30 days average"
                icon="check-circle"
                iconColor="bg-success-100"
                iconTextColor="text-success-600"
            />

            <x-layout.stat-card
                label="Products Sold"
                value="1,892"
                subtitle="This week"
                icon="shopping-cart"
                iconColor="bg-info-100"
                iconTextColor="text-info-600"
            />

            <x-layout.stat-card
                label="Notifications"
                value="47"
                subtitle="Unread messages"
                icon="bell"
                iconColor="bg-warning-100"
                iconTextColor="text-warning-600"
            />
        </div>

        <!-- Test 4: Stat cards with all features (subtitle + trend) -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">4. Stat Cards with All Features</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
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

            <x-layout.stat-card
                label="Customer Satisfaction"
                value="4.8/5.0"
                subtitle="Based on 1,234 reviews"
                icon="check-circle"
                iconColor="bg-primary-100"
                iconTextColor="text-primary-600"
                trend="+0.3"
                :trendUp="true"
            />

            <x-layout.stat-card
                label="Response Time"
                value="2.4 min"
                subtitle="Average response time"
                icon="clock"
                iconColor="bg-info-100"
                iconTextColor="text-info-600"
                trend="-15%"
                :trendUp="true"
            />
        </div>

        <!-- Test 5: Stat cards without icons -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">5. Stat Cards without Icons</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <x-layout.stat-card
                label="Total Orders"
                value="3,456"
            />

            <x-layout.stat-card
                label="Conversion Rate"
                value="3.24%"
                trend="+0.5%"
                :trendUp="true"
            />

            <x-layout.stat-card
                label="Bounce Rate"
                value="42.3%"
                subtitle="Last 7 days"
            />

            <x-layout.stat-card
                label="Page Views"
                value="125.4K"
                subtitle="This month"
                trend="+18.2%"
                :trendUp="true"
            />
        </div>

        <!-- Test 6: Different color variants -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">6. Different Color Variants</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-12">
            <x-layout.stat-card
                label="Primary Color"
                value="1,234"
                icon="chart-bar"
                iconColor="bg-primary-100"
                iconTextColor="text-primary-600"
            />

            <x-layout.stat-card
                label="Secondary Color"
                value="5,678"
                icon="users"
                iconColor="bg-secondary-100"
                iconTextColor="text-secondary-600"
            />

            <x-layout.stat-card
                label="Success Color"
                value="9,012"
                icon="check-circle"
                iconColor="bg-success-100"
                iconTextColor="text-success-600"
            />

            <x-layout.stat-card
                label="Danger Color"
                value="345"
                icon="x-circle"
                iconColor="bg-danger-100"
                iconTextColor="text-danger-600"
            />
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-12">
            <x-layout.stat-card
                label="Warning Color"
                value="678"
                icon="exclamation-triangle"
                iconColor="bg-warning-100"
                iconTextColor="text-warning-600"
            />

            <x-layout.stat-card
                label="Info Color"
                value="901"
                icon="information-circle"
                iconColor="bg-info-100"
                iconTextColor="text-info-600"
            />

            <x-layout.stat-card
                label="Gray Color"
                value="234"
                icon="document"
                iconColor="bg-gray-100"
                iconTextColor="text-gray-600"
            />
        </div>

        <!-- Test 7: Responsive grid layout test -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">7. Responsive Grid Layout (Resize browser to test)</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 mb-12">
            <x-layout.stat-card
                label="Mobile: 1 col"
                value="100%"
                icon="users"
                iconColor="bg-primary-100"
                iconTextColor="text-primary-600"
            />

            <x-layout.stat-card
                label="Tablet: 2 cols"
                value="50%"
                icon="chart-bar"
                iconColor="bg-success-100"
                iconTextColor="text-success-600"
            />

            <x-layout.stat-card
                label="Desktop: 3 cols"
                value="33%"
                icon="currency-dollar"
                iconColor="bg-info-100"
                iconTextColor="text-info-600"
            />

            <x-layout.stat-card
                label="XL: 4 cols"
                value="25%"
                icon="shopping-cart"
                iconColor="bg-warning-100"
                iconTextColor="text-warning-600"
            />
        </div>

        <!-- Test 8: Hover effect test -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">8. Hover Effect Test (Hover over cards)</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-12">
            <x-layout.stat-card
                label="Hover Me"
                value="Shadow increases"
                icon="cursor-arrow-rays"
                iconColor="bg-primary-100"
                iconTextColor="text-primary-600"
            />

            <x-layout.stat-card
                label="Smooth Transition"
                value="200ms duration"
                icon="clock"
                iconColor="bg-success-100"
                iconTextColor="text-success-600"
            />

            <x-layout.stat-card
                label="Interactive"
                value="User feedback"
                icon="hand-raised"
                iconColor="bg-info-100"
                iconTextColor="text-info-600"
            />
        </div>

        <!-- Test 9: Long text handling -->
        <h2 class="text-xl font-semibold text-gray-800 mb-4">9. Long Text Handling</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-12">
            <x-layout.stat-card
                label="This is a very long label that should truncate properly"
                value="999,999,999"
                subtitle="This is a very long subtitle that should wrap to multiple lines if needed"
                icon="document"
                iconColor="bg-gray-100"
                iconTextColor="text-gray-600"
                trend="+999.9%"
                :trendUp="true"
            />

            <x-layout.stat-card
                label="Short"
                value="1"
                subtitle="Brief"
                icon="check-circle"
                iconColor="bg-success-100"
                iconTextColor="text-success-600"
                trend="+1%"
                :trendUp="true"
            />
        </div>

        <div class="mt-8 p-4 bg-white rounded-lg shadow">
            <h3 class="text-lg font-semibold text-gray-900 mb-2">Test Summary</h3>
            <ul class="list-disc list-inside text-sm text-gray-700 space-y-1">
                <li>✓ Label, value, and subtitle props working</li>
                <li>✓ Icon with customizable background and text colors</li>
                <li>✓ Trend indicator with up/down arrows</li>
                <li>✓ Hover effect with shadow transition</li>
                <li>✓ Responsive grid layouts tested</li>
                <li>✓ All color variants working</li>
                <li>✓ Component works with and without optional props</li>
                <li>✓ Long text handling with truncation</li>
            </ul>
        </div>
    </div>
</body>
</html>
