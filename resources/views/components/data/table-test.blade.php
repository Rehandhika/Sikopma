<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Table Components Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="bg-white p-6 rounded-lg shadow">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Table Components Test</h1>
            <p class="text-gray-600">Testing table, table-row, and table-cell components</p>
        </div>

        <!-- Test 1: Basic Table with Headers Array -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">1. Basic Table with Headers Array</h2>
            <x-data.table :headers="['Name', 'Email', 'Role', 'Status']">
                <x-data.table-row>
                    <x-data.table-cell>John Doe</x-data.table-cell>
                    <x-data.table-cell>john@example.com</x-data.table-cell>
                    <x-data.table-cell>Admin</x-data.table-cell>
                    <x-data.table-cell>
                        <span class="px-2 py-1 text-xs font-medium bg-success-100 text-success-700 rounded-full">Active</span>
                    </x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>Jane Smith</x-data.table-cell>
                    <x-data.table-cell>jane@example.com</x-data.table-cell>
                    <x-data.table-cell>Manager</x-data.table-cell>
                    <x-data.table-cell>
                        <span class="px-2 py-1 text-xs font-medium bg-success-100 text-success-700 rounded-full">Active</span>
                    </x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>Bob Johnson</x-data.table-cell>
                    <x-data.table-cell>bob@example.com</x-data.table-cell>
                    <x-data.table-cell>Employee</x-data.table-cell>
                    <x-data.table-cell>
                        <span class="px-2 py-1 text-xs font-medium bg-warning-100 text-warning-700 rounded-full">Pending</span>
                    </x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        </div>

        <!-- Test 2: Striped Table (Default) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">2. Striped Table (Default Behavior)</h2>
            <x-data.table :headers="['Product', 'Category', 'Price', 'Stock']">
                <x-data.table-row>
                    <x-data.table-cell>Laptop</x-data.table-cell>
                    <x-data.table-cell>Electronics</x-data.table-cell>
                    <x-data.table-cell>$999.00</x-data.table-cell>
                    <x-data.table-cell>15</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>Mouse</x-data.table-cell>
                    <x-data.table-cell>Accessories</x-data.table-cell>
                    <x-data.table-cell>$29.99</x-data.table-cell>
                    <x-data.table-cell>50</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>Keyboard</x-data.table-cell>
                    <x-data.table-cell>Accessories</x-data.table-cell>
                    <x-data.table-cell>$79.99</x-data.table-cell>
                    <x-data.table-cell>30</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>Monitor</x-data.table-cell>
                    <x-data.table-cell>Electronics</x-data.table-cell>
                    <x-data.table-cell>$299.00</x-data.table-cell>
                    <x-data.table-cell>20</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>Webcam</x-data.table-cell>
                    <x-data.table-cell>Accessories</x-data.table-cell>
                    <x-data.table-cell>$59.99</x-data.table-cell>
                    <x-data.table-cell>25</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        </div>

        <!-- Test 3: Non-Striped Table -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">3. Non-Striped Table</h2>
            <x-data.table :headers="['ID', 'Transaction', 'Amount', 'Date']" :striped="false">
                <x-data.table-row :striped="false">
                    <x-data.table-cell>#001</x-data.table-cell>
                    <x-data.table-cell>Payment Received</x-data.table-cell>
                    <x-data.table-cell class="text-success-600 font-semibold">+$500.00</x-data.table-cell>
                    <x-data.table-cell>2024-01-15</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row :striped="false">
                    <x-data.table-cell>#002</x-data.table-cell>
                    <x-data.table-cell>Refund Issued</x-data.table-cell>
                    <x-data.table-cell class="text-danger-600 font-semibold">-$50.00</x-data.table-cell>
                    <x-data.table-cell>2024-01-16</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row :striped="false">
                    <x-data.table-cell>#003</x-data.table-cell>
                    <x-data.table-cell>Payment Received</x-data.table-cell>
                    <x-data.table-cell class="text-success-600 font-semibold">+$1,200.00</x-data.table-cell>
                    <x-data.table-cell>2024-01-17</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        </div>

        <!-- Test 4: Hoverable Table (Default) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">4. Hoverable Table (Hover to See Effect)</h2>
            <p class="text-sm text-gray-600 mb-3">Hover over rows to see the hover effect</p>
            <x-data.table :headers="['Course', 'Instructor', 'Students', 'Duration']">
                <x-data.table-row>
                    <x-data.table-cell>Web Development</x-data.table-cell>
                    <x-data.table-cell>John Smith</x-data.table-cell>
                    <x-data.table-cell>45</x-data.table-cell>
                    <x-data.table-cell>12 weeks</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>Data Science</x-data.table-cell>
                    <x-data.table-cell>Sarah Johnson</x-data.table-cell>
                    <x-data.table-cell>30</x-data.table-cell>
                    <x-data.table-cell>16 weeks</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>Mobile Development</x-data.table-cell>
                    <x-data.table-cell>Mike Brown</x-data.table-cell>
                    <x-data.table-cell>25</x-data.table-cell>
                    <x-data.table-cell>10 weeks</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        </div>

        <!-- Test 5: Non-Hoverable Table -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">5. Non-Hoverable Table</h2>
            <x-data.table :headers="['Item', 'Quantity', 'Price']" :hoverable="false">
                <x-data.table-row :hoverable="false">
                    <x-data.table-cell>Item A</x-data.table-cell>
                    <x-data.table-cell>10</x-data.table-cell>
                    <x-data.table-cell>$100.00</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row :hoverable="false">
                    <x-data.table-cell>Item B</x-data.table-cell>
                    <x-data.table-cell>5</x-data.table-cell>
                    <x-data.table-cell>$50.00</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        </div>

        <!-- Test 6: Table Without Headers -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">6. Table Without Headers</h2>
            <x-data.table>
                <x-data.table-row>
                    <x-data.table-cell header>Label 1:</x-data.table-cell>
                    <x-data.table-cell>Value 1</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell header>Label 2:</x-data.table-cell>
                    <x-data.table-cell>Value 2</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell header>Label 3:</x-data.table-cell>
                    <x-data.table-cell>Value 3</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        </div>

        <!-- Test 7: Responsive Table (Horizontal Scroll on Mobile) -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">7. Responsive Table (Resize Window to Test)</h2>
            <p class="text-sm text-gray-600 mb-3">This table will scroll horizontally on small screens</p>
            <x-data.table :headers="['ID', 'Name', 'Email', 'Phone', 'Address', 'City', 'State', 'Zip', 'Country', 'Status']">
                <x-data.table-row>
                    <x-data.table-cell>1</x-data.table-cell>
                    <x-data.table-cell>John Doe</x-data.table-cell>
                    <x-data.table-cell>john@example.com</x-data.table-cell>
                    <x-data.table-cell>555-1234</x-data.table-cell>
                    <x-data.table-cell>123 Main St</x-data.table-cell>
                    <x-data.table-cell>New York</x-data.table-cell>
                    <x-data.table-cell>NY</x-data.table-cell>
                    <x-data.table-cell>10001</x-data.table-cell>
                    <x-data.table-cell>USA</x-data.table-cell>
                    <x-data.table-cell>
                        <span class="px-2 py-1 text-xs font-medium bg-success-100 text-success-700 rounded-full">Active</span>
                    </x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>2</x-data.table-cell>
                    <x-data.table-cell>Jane Smith</x-data.table-cell>
                    <x-data.table-cell>jane@example.com</x-data.table-cell>
                    <x-data.table-cell>555-5678</x-data.table-cell>
                    <x-data.table-cell>456 Oak Ave</x-data.table-cell>
                    <x-data.table-cell>Los Angeles</x-data.table-cell>
                    <x-data.table-cell>CA</x-data.table-cell>
                    <x-data.table-cell>90001</x-data.table-cell>
                    <x-data.table-cell>USA</x-data.table-cell>
                    <x-data.table-cell>
                        <span class="px-2 py-1 text-xs font-medium bg-success-100 text-success-700 rounded-full">Active</span>
                    </x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        </div>

        <!-- Test 8: Large Dataset Table -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">8. Large Dataset Table (50 Rows)</h2>
            <p class="text-sm text-gray-600 mb-3">Testing performance with larger dataset</p>
            <x-data.table :headers="['#', 'Name', 'Department', 'Salary', 'Hire Date']">
                @for($i = 1; $i <= 50; $i++)
                <x-data.table-row>
                    <x-data.table-cell>{{ $i }}</x-data.table-cell>
                    <x-data.table-cell>Employee {{ $i }}</x-data.table-cell>
                    <x-data.table-cell>{{ ['Engineering', 'Sales', 'Marketing', 'HR', 'Finance'][($i - 1) % 5] }}</x-data.table-cell>
                    <x-data.table-cell>${{ number_format(50000 + ($i * 1000), 2) }}</x-data.table-cell>
                    <x-data.table-cell>{{ date('Y-m-d', strtotime('-' . $i . ' days')) }}</x-data.table-cell>
                </x-data.table-row>
                @endfor
            </x-data.table>
        </div>

        <!-- Test 9: Table with Custom Classes -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">9. Table with Custom Classes</h2>
            <x-data.table :headers="['Feature', 'Status']" class="border-2 border-primary-200">
                <x-data.table-row>
                    <x-data.table-cell class="font-bold">Striped Rows</x-data.table-cell>
                    <x-data.table-cell class="text-success-600">✓ Implemented</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell class="font-bold">Hoverable Rows</x-data.table-cell>
                    <x-data.table-cell class="text-success-600">✓ Implemented</x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell class="font-bold">Responsive Design</x-data.table-cell>
                    <x-data.table-cell class="text-success-600">✓ Implemented</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        </div>

        <!-- Test 10: Table with Actions Column -->
        <div class="bg-white p-6 rounded-lg shadow">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">10. Table with Actions Column</h2>
            <x-data.table :headers="['User', 'Email', 'Role', 'Actions']">
                <x-data.table-row>
                    <x-data.table-cell>Alice Johnson</x-data.table-cell>
                    <x-data.table-cell>alice@example.com</x-data.table-cell>
                    <x-data.table-cell>Admin</x-data.table-cell>
                    <x-data.table-cell>
                        <div class="flex space-x-2">
                            <button class="text-primary-600 hover:text-primary-800 text-sm font-medium">Edit</button>
                            <button class="text-danger-600 hover:text-danger-800 text-sm font-medium">Delete</button>
                        </div>
                    </x-data.table-cell>
                </x-data.table-row>
                <x-data.table-row>
                    <x-data.table-cell>Bob Wilson</x-data.table-cell>
                    <x-data.table-cell>bob@example.com</x-data.table-cell>
                    <x-data.table-cell>User</x-data.table-cell>
                    <x-data.table-cell>
                        <div class="flex space-x-2">
                            <button class="text-primary-600 hover:text-primary-800 text-sm font-medium">Edit</button>
                            <button class="text-danger-600 hover:text-danger-800 text-sm font-medium">Delete</button>
                        </div>
                    </x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        </div>

        <!-- Summary -->
        <div class="bg-primary-50 border-l-4 border-primary-500 p-6 rounded-lg">
            <h2 class="text-lg font-semibold text-primary-900 mb-2">✓ All Tests Complete</h2>
            <ul class="text-sm text-primary-800 space-y-1">
                <li>✓ Table component with headers array</li>
                <li>✓ Table-row component with striped option</li>
                <li>✓ Table-cell component (td and th variants)</li>
                <li>✓ Striped rows (odd/even coloring)</li>
                <li>✓ Hoverable rows with transition</li>
                <li>✓ Responsive horizontal scroll on mobile</li>
                <li>✓ Large dataset performance (50+ rows)</li>
                <li>✓ Custom class support</li>
                <li>✓ Flexible content (badges, buttons, etc.)</li>
            </ul>
        </div>
    </div>
</body>
</html>
