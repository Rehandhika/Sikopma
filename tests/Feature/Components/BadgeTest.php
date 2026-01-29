<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class BadgeTest extends TestCase
{
    /**
     * Test that badge component renders without errors
     */
    public function test_badge_component_renders(): void
    {
        $view = $this->blade('<x-ui.badge>Active</x-ui.badge>');

        $view->assertSee('Active');
        $view->assertSee('rounded-full');
    }

    /**
     * Test that badge component renders all variants
     */
    public function test_badge_component_renders_all_variants(): void
    {
        $variants = ['default', 'primary', 'success', 'danger', 'warning', 'info'];

        foreach ($variants as $variant) {
            $view = $this->blade(
                '<x-ui.badge :variant="$variant">Test</x-ui.badge>',
                ['variant' => $variant]
            );

            $view->assertSee('rounded-full');
            $view->assertSee('Test');
        }
    }

    /**
     * Test that badge component renders all sizes
     */
    public function test_badge_component_renders_all_sizes(): void
    {
        $sizes = ['sm', 'md'];

        foreach ($sizes as $size) {
            $view = $this->blade(
                '<x-ui.badge :size="$size">Test</x-ui.badge>',
                ['size' => $size]
            );

            $view->assertSee('rounded-full');
            $view->assertSee('Test');
        }
    }

    /**
     * Test that badge component supports dot indicator
     */
    public function test_badge_component_supports_dot_indicator(): void
    {
        $view = $this->blade('<x-ui.badge :dot="true" variant="success">Active</x-ui.badge>');

        $view->assertSee('Active');
        $view->assertSee('bg-emerald-400');
    }

    /**
     * Test that badge component supports icon
     */
    public function test_badge_component_supports_icon(): void
    {
        $view = $this->blade('<x-ui.badge icon="check">Verified</x-ui.badge>');

        $view->assertSee('Verified');
        // Check for SVG element (icon is rendered as SVG)
        $view->assertSee('<svg', false);
        $view->assertSee('w-4 h-4');
    }

    /**
     * Test that badge component supports removable with close button
     */
    public function test_badge_component_supports_removable(): void
    {
        $view = $this->blade('<x-ui.badge :removable="true">Tag</x-ui.badge>');

        $view->assertSee('Tag');
        // Check for button element and SVG icon
        $view->assertSee('type="button"', false);
        $view->assertSee('<svg', false);
        $view->assertSee('aria-label="Remove"', false);
    }

    /**
     * Test that badge component supports removable with wire:click action
     */
    public function test_badge_component_supports_removable_with_action(): void
    {
        $view = $this->blade('<x-ui.badge :removable="true" onRemove="removeTag">Tag</x-ui.badge>');

        $view->assertSee('Tag');
        $view->assertSee('wire:click="removeTag"', false);
    }

    /**
     * Test that badge component has dark mode support
     */
    public function test_badge_component_has_dark_mode_support(): void
    {
        $view = $this->blade('<x-ui.badge variant="success">Active</x-ui.badge>');

        $view->assertSee('dark:bg-emerald-900/30');
        $view->assertSee('dark:text-emerald-400');
        $view->assertSee('dark:border-emerald-700');
    }

    /**
     * Test that badge component uses consistent rounded-full
     */
    public function test_badge_component_has_consistent_rounded_full(): void
    {
        $view = $this->blade('<x-ui.badge>Test</x-ui.badge>');

        $view->assertSee('rounded-full');
    }

    /**
     * Test that badge component supports all features together
     */
    public function test_badge_component_supports_all_features_together(): void
    {
        $view = $this->blade(
            '<x-ui.badge :dot="true" icon="check" :removable="true" variant="success" size="md">Complete</x-ui.badge>'
        );

        $view->assertSee('Complete');
        $view->assertSee('rounded-full');
        $view->assertSee('bg-emerald-400'); // dot color
        // Check for SVG elements (icons are rendered as SVG)
        $view->assertSee('<svg', false);
        $view->assertSee('type="button"', false); // remove button
    }
}
