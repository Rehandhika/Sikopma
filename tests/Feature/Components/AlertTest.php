<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class AlertTest extends TestCase
{
    /**
     * Test that alert component renders without errors
     */
    public function test_alert_component_renders(): void
    {
        $view = $this->blade('<x-ui.alert>This is an alert message</x-ui.alert>');

        $view->assertSee('This is an alert message');
        $view->assertSee('border-l-4');
        $view->assertSee('rounded-lg');
    }

    /**
     * Test that alert component renders all variants with correct icons
     */
    public function test_alert_component_renders_all_variants_with_correct_icons(): void
    {
        $variants = ['success', 'danger', 'warning', 'info'];

        foreach ($variants as $variant) {
            $view = $this->blade(
                '<x-ui.alert :variant="$variant">Test message</x-ui.alert>',
                ['variant' => $variant]
            );

            $view->assertSee('Test message');
            $view->assertSee('border-l-4');
            // Check for SVG icon presence
            $view->assertSee('<svg', false);
            $view->assertSee('h-5 w-5');
        }
    }

    /**
     * Test that alert component supports dismissible with close button
     */
    public function test_alert_component_supports_dismissible(): void
    {
        $view = $this->blade('<x-ui.alert :dismissible="true">Dismissible alert</x-ui.alert>');

        $view->assertSee('Dismissible alert');
        $view->assertSee('@click="show = false"', false);
        $view->assertSee('type="button"', false);
    }

    /**
     * Test that alert component has Alpine.js transitions for dismissible
     */
    public function test_alert_component_has_transitions_for_dismissible(): void
    {
        $view = $this->blade('<x-ui.alert :dismissible="true">Alert with animation</x-ui.alert>');

        $view->assertSee('x-transition:enter', false);
        $view->assertSee('x-transition:leave', false);
        $view->assertSee('x-data="{ show: true }"', false);
    }

    /**
     * Test that alert component has consistent border-l-4 accent
     */
    public function test_alert_component_has_consistent_border_accent(): void
    {
        $variants = ['success', 'danger', 'warning', 'info'];

        foreach ($variants as $variant) {
            $view = $this->blade(
                '<x-ui.alert :variant="$variant">Test</x-ui.alert>',
                ['variant' => $variant]
            );

            $view->assertSee('border-l-4');
        }
    }

    /**
     * Test that alert component uses semantic colors for each variant
     */
    public function test_alert_component_uses_semantic_colors(): void
    {
        $variantColors = [
            'success' => ['bg-success-50', 'border-success-200', 'text-success-800'],
            'danger' => ['bg-danger-50', 'border-danger-200', 'text-danger-800'],
            'warning' => ['bg-warning-50', 'border-warning-200', 'text-warning-800'],
            'info' => ['bg-info-50', 'border-info-200', 'text-info-800'],
        ];

        foreach ($variantColors as $variant => $colors) {
            $view = $this->blade(
                '<x-ui.alert :variant="$variant">Test</x-ui.alert>',
                ['variant' => $variant]
            );

            foreach ($colors as $color) {
                $view->assertSee($color);
            }
        }
    }

    /**
     * Test that alert component has dark mode support
     */
    public function test_alert_component_has_dark_mode_support(): void
    {
        $view = $this->blade('<x-ui.alert variant="success">Success message</x-ui.alert>');

        $view->assertSee('dark:bg-success-900/30');
        $view->assertSee('dark:border-success-700');
        $view->assertSee('dark:text-success-400');
    }

    /**
     * Test that alert component supports optional title
     */
    public function test_alert_component_supports_optional_title(): void
    {
        $view = $this->blade(
            '<x-ui.alert title="Important Notice">This is the alert content</x-ui.alert>'
        );

        $view->assertSee('Important Notice');
        $view->assertSee('This is the alert content');
        $view->assertSee('font-medium');
    }

    /**
     * Test that alert component can hide icon
     */
    public function test_alert_component_can_hide_icon(): void
    {
        $view = $this->blade('<x-ui.alert :icon="false">Alert without icon</x-ui.alert>');

        $view->assertSee('Alert without icon');
        $view->assertDontSee('flex-shrink-0');
    }

    /**
     * Test that alert component supports all features together
     */
    public function test_alert_component_supports_all_features_together(): void
    {
        $view = $this->blade(
            '<x-ui.alert variant="warning" :dismissible="true" title="Warning">Please review this carefully</x-ui.alert>'
        );

        $view->assertSee('Warning');
        $view->assertSee('Please review this carefully');
        $view->assertSee('border-l-4');
        // Check for SVG icon presence
        $view->assertSee('<svg', false);
        $view->assertSee('@click="show = false"', false);
    }
}
