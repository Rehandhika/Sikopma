<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class CardTest extends TestCase
{
    /**
     * Test that card component renders with default props
     */
    public function test_card_component_renders_with_defaults(): void
    {
        $view = $this->blade('<x-ui.card>Test Content</x-ui.card>');

        $view->assertSee('Test Content');
        $view->assertSee('rounded-xl'); // Requirement 8.1
        $view->assertSee('bg-white dark:bg-gray-800', false); // Requirement 8.5
        $view->assertSee('border-gray-200 dark:border-gray-700', false); // Requirement 8.2
    }

    /**
     * Test that card component renders with padding disabled
     */
    public function test_card_component_renders_without_padding(): void
    {
        $view = $this->blade('<x-ui.card :padding="false">Test Content</x-ui.card>');

        $view->assertSee('Test Content');
        $view->assertDontSee('px-6 py-4', false);
    }

    /**
     * Test that card component renders with hover state
     */
    public function test_card_component_renders_with_hover_state(): void
    {
        $view = $this->blade('<x-ui.card :hover="true">Test Content</x-ui.card>');

        $view->assertSee('hover:shadow-lg', false);
        $view->assertSee('hover:border-gray-300', false);
    }

    /**
     * Test that card component renders with clickable cursor
     */
    public function test_card_component_renders_with_clickable_cursor(): void
    {
        $view = $this->blade('<x-ui.card :clickable="true">Test Content</x-ui.card>');

        $view->assertSee('cursor-pointer', false);
    }

    /**
     * Test that card component renders without border
     */
    public function test_card_component_renders_without_border(): void
    {
        $view = $this->blade('<x-ui.card :bordered="false">Test Content</x-ui.card>');

        $view->assertDontSee('border border-gray-200', false);
    }

    /**
     * Test that card component renders with header slot
     */
    public function test_card_component_renders_with_header_slot(): void
    {
        $view = $this->blade('
            <x-ui.card>
                <x-slot:header>
                    <h2>Header Content</h2>
                </x-slot:header>
                Body Content
            </x-ui.card>
        ');

        $view->assertSee('Header Content');
        $view->assertSee('Body Content');
        $view->assertSee('border-b', false); // Header should have bottom border
    }

    /**
     * Test that card component renders with footer slot
     */
    public function test_card_component_renders_with_footer_slot(): void
    {
        $view = $this->blade('
            <x-ui.card>
                Body Content
                <x-slot:footer>
                    <button>Footer Button</button>
                </x-slot:footer>
            </x-ui.card>
        ');

        $view->assertSee('Body Content');
        $view->assertSee('Footer Button');
        $view->assertSee('border-t', false); // Footer should have top border
    }

    /**
     * Test that card component renders with both header and footer slots
     */
    public function test_card_component_renders_with_header_and_footer_slots(): void
    {
        $view = $this->blade('
            <x-ui.card>
                <x-slot:header>Header</x-slot:header>
                Body
                <x-slot:footer>Footer</x-slot:footer>
            </x-ui.card>
        ');

        $view->assertSee('Header');
        $view->assertSee('Body');
        $view->assertSee('Footer');
    }

    /**
     * Test that card component renders with legacy title and subtitle
     */
    public function test_card_component_renders_with_legacy_title_and_subtitle(): void
    {
        $view = $this->blade('
            <x-ui.card title="Card Title" subtitle="Card Subtitle">
                Body Content
            </x-ui.card>
        ');

        $view->assertSee('Card Title');
        $view->assertSee('Card Subtitle');
        $view->assertSee('Body Content');
    }

    /**
     * Test that card component renders with all features combined
     */
    public function test_card_component_renders_with_all_features_combined(): void
    {
        $view = $this->blade('
            <x-ui.card 
                :hover="true" 
                :clickable="true" 
                shadow="lg">
                <x-slot:header>Header</x-slot:header>
                Body
                <x-slot:footer>Footer</x-slot:footer>
            </x-ui.card>
        ');

        $view->assertSee('hover:shadow-lg', false);
        $view->assertSee('cursor-pointer', false);
        $view->assertSee('shadow-lg', false);
        $view->assertSee('Header');
        $view->assertSee('Body');
        $view->assertSee('Footer');
    }

    /**
     * Test that card component supports dark mode classes
     */
    public function test_card_component_supports_dark_mode(): void
    {
        $view = $this->blade('<x-ui.card>Test</x-ui.card>');

        $view->assertSee('dark:bg-gray-800', false); // Requirement 8.5
        $view->assertSee('dark:border-gray-700', false); // Requirement 8.2
    }

    /**
     * Test that card component uses consistent border-radius
     */
    public function test_card_component_uses_consistent_border_radius(): void
    {
        $view = $this->blade('<x-ui.card>Test</x-ui.card>');

        $view->assertSee('rounded-xl', false); // Requirement 8.1
    }

    /**
     * Test that card component supports optional padding configuration
     */
    public function test_card_component_supports_optional_padding_configuration(): void
    {
        // With padding (default)
        $viewWithPadding = $this->blade('<x-ui.card :padding="true">Test</x-ui.card>');
        $viewWithPadding->assertSee('px-6 py-4', false);

        // Without padding
        $viewWithoutPadding = $this->blade('<x-ui.card :padding="false">Test</x-ui.card>');
        $viewWithoutPadding->assertDontSee('px-6 py-4', false);
    }

    /**
     * Test that card component supports hover state for interactive cards
     */
    public function test_card_component_supports_hover_state_for_interactive_cards(): void
    {
        $view = $this->blade('<x-ui.card :hover="true" :clickable="true">Interactive Card</x-ui.card>');

        $view->assertSee('hover:shadow-lg', false); // Requirement 8.6
        $view->assertSee('cursor-pointer', false); // Requirement 8.6
        $view->assertSee('Interactive Card');
    }
}
