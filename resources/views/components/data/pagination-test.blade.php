<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pagination Component Test</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-8">
    <div class="max-w-7xl mx-auto space-y-8">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Pagination Component Test</h1>
            <p class="text-gray-600 mb-6">Testing the pagination component with Laravel pagination integration</p>
        </div>

        {{-- Test Case 1: Basic Pagination with Table --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test Case 1: Pagination with Table</h2>
            <p class="text-sm text-gray-600 mb-4">Simulates a typical data table with pagination</p>
            
            <x-data.table :headers="['ID', 'Name', 'Email', 'Status', 'Actions']">
                @for($i = 1; $i <= 10; $i++)
                <x-data.table-row>
                    <x-data.table-cell>{{ $i }}</x-data.table-cell>
                    <x-data.table-cell>User {{ $i }}</x-data.table-cell>
                    <x-data.table-cell>user{{ $i }}@example.com</x-data.table-cell>
                    <x-data.table-cell>
                        <x-ui.badge variant="success" size="sm">Active</x-ui.badge>
                    </x-data.table-cell>
                    <x-data.table-cell>
                        <button class="text-primary-600 hover:text-primary-800 text-sm">Edit</button>
                    </x-data.table-cell>
                </x-data.table-row>
                @endfor
            </x-data.table>

            <div class="mt-4">
                {{-- Mock paginator object for demonstration --}}
                <div class="text-sm text-gray-600 mb-2">
                    <strong>Note:</strong> In production, pass the actual Laravel paginator object:
                    <code class="bg-gray-100 px-2 py-1 rounded">{{ '<x-data.pagination :paginator="$users" />' }}</code>
                </div>
                
                {{-- Visual representation of pagination --}}
                <nav role="navigation" aria-label="Pagination Navigation" class="flex items-center justify-between">
                    {{-- Mobile View --}}
                    <div class="flex justify-between flex-1 sm:hidden">
                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed leading-5 rounded-md">
                            Previous
                        </span>
                        <span class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700">
                            Page 1 of 5
                        </span>
                        <a href="#" class="relative inline-flex items-center px-4 py-2 ml-3 text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 rounded-md hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                            Next
                        </a>
                    </div>

                    {{-- Desktop View --}}
                    <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
                        <div>
                            <p class="text-sm text-gray-700 leading-5">
                                Showing
                                <span class="font-medium">1</span>
                                to
                                <span class="font-medium">10</span>
                                of
                                <span class="font-medium">50</span>
                                results
                            </p>
                        </div>

                        <div>
                            <span class="relative z-0 inline-flex rounded-lg shadow-sm">
                                {{-- Previous (disabled) --}}
                                <span aria-disabled="true" aria-label="Previous">
                                    <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed rounded-l-lg leading-5" aria-hidden="true">
                                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                        </svg>
                                    </span>
                                </span>

                                {{-- Page 1 (current) --}}
                                <span aria-current="page">
                                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-primary-600 border border-primary-600 cursor-default leading-5">1</span>
                                </span>

                                {{-- Page 2 --}}
                                <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    2
                                </a>

                                {{-- Page 3 --}}
                                <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    3
                                </a>

                                {{-- Dots --}}
                                <span aria-disabled="true">
                                    <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">...</span>
                                </span>

                                {{-- Page 5 --}}
                                <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500 focus:z-10 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 active:bg-gray-100 active:text-gray-700 transition ease-in-out duration-150">
                                    5
                                </a>

                                {{-- Next --}}
                                <a href="#" rel="next" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-lg leading-5 hover:text-gray-400 focus:z-10 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-primary-500 active:bg-gray-100 active:text-gray-500 transition ease-in-out duration-150" aria-label="Next">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </a>
                            </span>
                        </div>
                    </div>
                </nav>
            </div>
        </div>

        {{-- Test Case 2: Responsive Behavior --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test Case 2: Responsive Behavior</h2>
            <p class="text-sm text-gray-600 mb-4">Resize your browser to see mobile vs desktop pagination</p>
            
            <div class="space-y-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Mobile View (< 640px)</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Shows Previous/Next buttons only</li>
                        <li>• Displays current page number (e.g., "Page 1 of 5")</li>
                        <li>• Simplified layout for small screens</li>
                    </ul>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Desktop View (≥ 640px)</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Shows full pagination with page numbers</li>
                        <li>• Displays result count (e.g., "Showing 1 to 10 of 50 results")</li>
                        <li>• Uses "..." for large page ranges</li>
                        <li>• Current page highlighted with primary color</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Test Case 3: Different Page Counts --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Test Case 3: Different Page Count Scenarios</h2>
            <p class="text-sm text-gray-600 mb-4">Examples of pagination with different total page counts</p>
            
            <div class="space-y-6">
                {{-- Few pages (1-3) --}}
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Scenario A: Few Pages (3 pages)</h3>
                    <nav class="hidden sm:flex sm:items-center sm:justify-center">
                        <span class="relative z-0 inline-flex rounded-lg shadow-sm">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed rounded-l-lg leading-5">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-primary-600 border border-primary-600 cursor-default leading-5">1</span>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500">2</a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500">3</a>
                            <a href="#" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-lg leading-5 hover:text-gray-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </span>
                    </nav>
                    <p class="text-xs text-gray-500 mt-2">All pages shown, no ellipsis needed</p>
                </div>

                {{-- Medium pages (5-10) --}}
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Scenario B: Medium Pages (7 pages, on page 4)</h3>
                    <nav class="hidden sm:flex sm:items-center sm:justify-center">
                        <span class="relative z-0 inline-flex rounded-lg shadow-sm">
                            <a href="#" class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-lg leading-5 hover:text-gray-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500">1</a>
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">...</span>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500">3</a>
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-primary-600 border border-primary-600 cursor-default leading-5">4</span>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500">5</a>
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">...</span>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500">7</a>
                            <a href="#" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-lg leading-5 hover:text-gray-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </span>
                    </nav>
                    <p class="text-xs text-gray-500 mt-2">Shows current page with context, uses ellipsis for gaps</p>
                </div>

                {{-- Many pages (10+) --}}
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Scenario C: Many Pages (20 pages, on page 1)</h3>
                    <nav class="hidden sm:flex sm:items-center sm:justify-center">
                        <span class="relative z-0 inline-flex rounded-lg shadow-sm">
                            <span class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 cursor-not-allowed rounded-l-lg leading-5">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
                                </svg>
                            </span>
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-white bg-primary-600 border border-primary-600 cursor-default leading-5">1</span>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500">2</a>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500">3</a>
                            <span class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 cursor-default leading-5">...</span>
                            <a href="#" class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 leading-5 hover:text-gray-500">20</a>
                            <a href="#" class="relative inline-flex items-center px-2 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-lg leading-5 hover:text-gray-400">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd" />
                                </svg>
                            </a>
                        </span>
                    </nav>
                    <p class="text-xs text-gray-500 mt-2">Shows first few pages and last page with ellipsis</p>
                </div>
            </div>
        </div>

        {{-- Usage Instructions --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Usage Instructions</h2>
            
            <div class="space-y-4">
                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Basic Usage</h3>
                    <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>{{-- In your Livewire component --}}
public function render()
{
    $users = User::paginate(10);
    return view('livewire.users.index', compact('users'));
}

{{-- In your Blade view --}}
&lt;x-data.table :headers="['Name', 'Email', 'Status']"&gt;
    @foreach($users as $user)
        &lt;x-data.table-row&gt;
            &lt;x-data.table-cell&gt;{{ $user->name }}&lt;/x-data.table-cell&gt;
            &lt;x-data.table-cell&gt;{{ $user->email }}&lt;/x-data.table-cell&gt;
            &lt;x-data.table-cell&gt;{{ $user->status }}&lt;/x-data.table-cell&gt;
        &lt;/x-data.table-row&gt;
    @endforeach
&lt;/x-data.table&gt;

&lt;x-data.pagination :paginator="$users" /&gt;</code></pre>
                </div>

                <div>
                    <h3 class="font-medium text-gray-900 mb-2">With Custom Classes</h3>
                    <pre class="bg-gray-900 text-gray-100 p-4 rounded-lg overflow-x-auto text-sm"><code>&lt;x-data.pagination :paginator="$users" class="mt-6" /&gt;</code></pre>
                </div>

                <div>
                    <h3 class="font-medium text-gray-900 mb-2">Features</h3>
                    <ul class="list-disc list-inside text-sm text-gray-600 space-y-1">
                        <li>Fully integrated with Laravel's pagination system</li>
                        <li>Responsive design (mobile and desktop views)</li>
                        <li>Accessible with ARIA labels and semantic HTML</li>
                        <li>Smooth transitions and hover effects</li>
                        <li>Focus states for keyboard navigation</li>
                        <li>Disabled states for first/last pages</li>
                        <li>Shows result count on desktop</li>
                        <li>Automatically handles ellipsis for large page counts</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Accessibility Features --}}
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-semibold text-gray-900 mb-4">Accessibility Features</h2>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Semantic HTML</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Uses <code class="bg-gray-100 px-1 rounded">&lt;nav&gt;</code> element</li>
                        <li>• Proper <code class="bg-gray-100 px-1 rounded">role="navigation"</code></li>
                        <li>• Descriptive <code class="bg-gray-100 px-1 rounded">aria-label</code></li>
                    </ul>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">ARIA Attributes</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• <code class="bg-gray-100 px-1 rounded">aria-current="page"</code> for active page</li>
                        <li>• <code class="bg-gray-100 px-1 rounded">aria-disabled</code> for disabled buttons</li>
                        <li>• <code class="bg-gray-100 px-1 rounded">aria-label</code> for navigation context</li>
                    </ul>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Keyboard Navigation</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Tab through page links</li>
                        <li>• Visible focus states</li>
                        <li>• Enter to activate links</li>
                    </ul>
                </div>

                <div class="border border-gray-200 rounded-lg p-4">
                    <h3 class="font-medium text-gray-900 mb-2">Visual Indicators</h3>
                    <ul class="text-sm text-gray-600 space-y-1">
                        <li>• Clear current page highlight</li>
                        <li>• Disabled state styling</li>
                        <li>• Hover effects for interactive elements</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
