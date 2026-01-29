<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class TableTest extends TestCase
{
    /**
     * Test that table component renders with default props
     */
    public function test_table_component_renders_with_defaults(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[\'Name\', \'Email\']">
                <x-data.table-row>
                    <x-data.table-cell>John Doe</x-data.table-cell>
                    <x-data.table-cell>john@example.com</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('Name');
        $view->assertSee('Email');
        $view->assertSee('John Doe');
        $view->assertSee('john@example.com');
        $view->assertSee('overflow-x-auto', false); // Requirement 9.3 - responsive
        $view->assertSee('bg-gray-50 dark:bg-gray-800', false); // Requirement 9.1 - header styling
    }

    /**
     * Test that table component renders with striped rows
     */
    public function test_table_component_renders_with_striped_rows(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[\'Name\']" :striped="true">
                <x-data.table-row>
                    <x-data.table-cell>Row 1</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('Row 1');
        $view->assertSee('divide-y-0', false); // Striped removes dividers
        $view->assertSee('bg-gray-50 dark:bg-gray-800/50', false); // Striped styling
    }

    /**
     * Test that table component renders with compact mode
     */
    public function test_table_component_renders_with_compact_mode(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[\'Name\']" :compact="true">
                <x-data.table-row>
                    <x-data.table-cell>Compact Row</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('Compact Row');
        $view->assertSee('px-4 py-2', false); // Compact padding in header
    }

    /**
     * Test that table component renders without responsive scroll
     */
    public function test_table_component_renders_without_responsive_scroll(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[\'Name\']" :responsive="false">
                <x-data.table-row>
                    <x-data.table-cell>Row</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('Row');
        $view->assertDontSee('overflow-x-auto', false);
    }

    /**
     * Test that table component renders with hoverable rows
     */
    public function test_table_component_renders_with_hoverable_rows(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[\'Name\']" :hoverable="true">
                <x-data.table-row>
                    <x-data.table-cell>Hoverable Row</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('Hoverable Row');
        $view->assertSee('bg-gray-100 dark:bg-gray-800', false); // Requirement 9.2 - hover state
    }

    /**
     * Test that table component renders without hoverable rows
     */
    public function test_table_component_renders_without_hoverable_rows(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[\'Name\']" :hoverable="false">
                <x-data.table-row>
                    <x-data.table-cell>Non-hoverable Row</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('Non-hoverable Row');
    }

    /**
     * Test that table component uses consistent header styling
     */
    public function test_table_component_uses_consistent_header_styling(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[\'Name\', \'Email\']">
                <x-data.table-row>
                    <x-data.table-cell>Test</x-data.table-cell>
                    <x-data.table-cell>test@example.com</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('bg-gray-50 dark:bg-gray-800', false); // Requirement 9.1 - gray background
        $view->assertSee('uppercase', false); // Requirement 9.1 - uppercase text
        $view->assertSee('text-xs', false); // Consistent typography
        $view->assertSee('font-medium', false); // Consistent typography
    }

    /**
     * Test that table component supports dark mode
     */
    public function test_table_component_supports_dark_mode(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[\'Name\']">
                <x-data.table-row>
                    <x-data.table-cell>Dark Mode Test</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('dark:bg-gray-800', false); // Header dark mode
        $view->assertSee('dark:bg-gray-900', false); // Body dark mode
        $view->assertSee('dark:divide-gray-700', false); // Divider dark mode
        $view->assertSee('dark:text-gray-400', false); // Text dark mode
    }

    /**
     * Test that table component uses consistent cell padding
     */
    public function test_table_component_uses_consistent_cell_padding(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[\'Name\']">
                <x-data.table-row>
                    <x-data.table-cell>Test</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('px-6 py-3', false); // Requirement 9.4 - header padding
    }

    /**
     * Test that table component renders with all features combined
     */
    public function test_table_component_renders_with_all_features_combined(): void
    {
        $view = $this->blade('
            <x-data.table 
                :headers="[\'Name\', \'Email\']" 
                :striped="true" 
                :hoverable="true" 
                :compact="true" 
                :responsive="true">
                <x-data.table-row>
                    <x-data.table-cell>John Doe</x-data.table-cell>
                    <x-data.table-cell>john@example.com</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('John Doe');
        $view->assertSee('john@example.com');
        $view->assertSee('overflow-x-auto', false); // Responsive
        $view->assertSee('px-4 py-2', false); // Compact
        $view->assertSee('divide-y-0', false); // Striped
    }

    /**
     * Test that table component renders without headers
     */
    public function test_table_component_renders_without_headers(): void
    {
        $view = $this->blade('
            <x-data.table>
                <x-data.table-row>
                    <x-data.table-cell>No Header Row</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('No Header Row');
        $view->assertDontSee('<thead', false);
    }

    /**
     * Test that table component renders with empty headers array
     */
    public function test_table_component_renders_with_empty_headers_array(): void
    {
        $view = $this->blade('
            <x-data.table :headers="[]">
                <x-data.table-row>
                    <x-data.table-cell>Empty Headers</x-data.table-cell>
                </x-data.table-row>
            </x-data.table>
        ');

        $view->assertSee('Empty Headers');
        $view->assertDontSee('<thead', false);
    }
}
