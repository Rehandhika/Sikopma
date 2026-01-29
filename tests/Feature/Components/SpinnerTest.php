<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class SpinnerTest extends TestCase
{
    /**
     * Test that spinner component renders without errors
     */
    public function test_spinner_component_renders(): void
    {
        $view = $this->blade('<x-ui.spinner />');

        $view->assertSee('animate-spin');
        $view->assertSee('svg', false);
    }

    /**
     * Test that spinner component renders all sizes
     *
     * @group Feature: admin-ui-standardization
     * Validates: Requirements 13.4
     */
    public function test_spinner_component_renders_all_sizes(): void
    {
        $sizes = ['sm', 'md', 'lg'];

        foreach ($sizes as $size) {
            $view = $this->blade(
                '<x-ui.spinner :size="$size" />',
                ['size' => $size]
            );

            $view->assertSee('animate-spin');
            $view->assertSee('svg', false);
        }
    }

    /**
     * Test that spinner component supports different colors
     */
    public function test_spinner_component_supports_colors(): void
    {
        $colors = ['primary', 'white', 'gray'];

        foreach ($colors as $color) {
            $view = $this->blade(
                '<x-ui.spinner :color="$color" />',
                ['color' => $color]
            );

            $view->assertSee('animate-spin');
        }
    }

    /**
     * Test that spinner component supports overlay mode
     *
     * @group Feature: admin-ui-standardization
     * Validates: Requirements 13.2
     */
    public function test_spinner_component_supports_overlay_mode(): void
    {
        $view = $this->blade('<x-ui.spinner :overlay="true" />');

        $view->assertSee('fixed inset-0');
        $view->assertSee('z-50');
        $view->assertSee('bg-gray-900/50');
        $view->assertSee('backdrop-blur-sm');
        $view->assertSee('animate-spin');
    }

    /**
     * Test that spinner overlay supports message
     */
    public function test_spinner_overlay_supports_message(): void
    {
        $view = $this->blade(
            '<x-ui.spinner :overlay="true" message="Loading..." />',
            ['message' => 'Loading...']
        );

        $view->assertSee('Loading...');
        $view->assertSee('fixed inset-0');
    }

    /**
     * Test that spinner component uses consistent animation
     *
     * @group Feature: admin-ui-standardization
     * Validates: Requirements 13.3
     */
    public function test_spinner_component_uses_consistent_animation(): void
    {
        $view = $this->blade('<x-ui.spinner />');

        $view->assertSee('animate-spin');
    }

    /**
     * Test that spinner component has dark mode support
     */
    public function test_spinner_component_has_dark_mode_support(): void
    {
        $view = $this->blade('<x-ui.spinner />');

        $view->assertSee('dark:text-primary-400');
    }

    /**
     * Test that spinner component handles invalid size gracefully
     */
    public function test_spinner_component_handles_invalid_size(): void
    {
        $view = $this->blade(
            '<x-ui.spinner size="invalid" />',
            ['size' => 'invalid']
        );

        // Should fall back to default (md)
        $view->assertSee('animate-spin');
        $view->assertSee('svg', false);
    }

    /**
     * Test that spinner component handles invalid color gracefully
     */
    public function test_spinner_component_handles_invalid_color(): void
    {
        $view = $this->blade(
            '<x-ui.spinner color="invalid" />',
            ['color' => 'invalid']
        );

        // Should fall back to default (primary)
        $view->assertSee('animate-spin');
        $view->assertSee('svg', false);
    }
}
