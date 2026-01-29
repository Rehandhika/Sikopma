<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class ButtonTest extends TestCase
{
    /**
     * Test that button component renders without errors
     */
    public function test_button_component_renders(): void
    {
        $view = $this->blade('<x-ui.button>Click me</x-ui.button>');
        
        $view->assertSee('Click me');
        $view->assertSee('type="button"', false);
    }
    
    /**
     * Test that button component renders all variants
     */
    public function test_button_component_renders_all_variants(): void
    {
        $variants = ['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'white', 'outline', 'ghost'];
        
        foreach ($variants as $variant) {
            $view = $this->blade(
                '<x-ui.button :variant="$variant">Test</x-ui.button>',
                ['variant' => $variant]
            );
            
            $view->assertSee('rounded-lg');
            $view->assertSee('Test');
        }
    }
    
    /**
     * Test that button component renders all sizes
     */
    public function test_button_component_renders_all_sizes(): void
    {
        $sizes = ['sm', 'md', 'lg'];
        
        foreach ($sizes as $size) {
            $view = $this->blade(
                '<x-ui.button :size="$size">Test</x-ui.button>',
                ['size' => $size]
            );
            
            $view->assertSee('rounded-lg');
            $view->assertSee('Test');
        }
    }
    
    /**
     * Test that button component supports icon with left position
     */
    public function test_button_component_supports_icon_left(): void
    {
        $view = $this->blade('<x-ui.button icon="plus" iconPosition="left">Add</x-ui.button>');
        
        $view->assertSee('Add');
        $view->assertSee('mr-2');
    }
    
    /**
     * Test that button component supports icon with right position
     */
    public function test_button_component_supports_icon_right(): void
    {
        $view = $this->blade('<x-ui.button icon="arrow-right" iconPosition="right">Next</x-ui.button>');
        
        $view->assertSee('Next');
        $view->assertSee('ml-2');
    }
    
    /**
     * Test that button component shows loading state
     */
    public function test_button_component_shows_loading_state(): void
    {
        $view = $this->blade('<x-ui.button :loading="true">Save</x-ui.button>');
        
        $view->assertSee('Save');
        $view->assertSee('disabled', false);
        $view->assertSee('animate-spin');
    }
    
    /**
     * Test that button component shows disabled state
     */
    public function test_button_component_shows_disabled_state(): void
    {
        $view = $this->blade('<x-ui.button :disabled="true">Disabled</x-ui.button>');
        
        $view->assertSee('Disabled');
        $view->assertSee('disabled', false);
        $view->assertSee('disabled:opacity-50');
        $view->assertSee('disabled:cursor-not-allowed');
    }
    
    /**
     * Test that button component renders as link when href is provided
     */
    public function test_button_component_renders_as_link(): void
    {
        $view = $this->blade('<x-ui.button href="/test">Link</x-ui.button>');
        
        $view->assertSee('Link');
        $view->assertSee('href="/test"', false);
        $view->assertDontSee('type="button"', false);
    }
    
    /**
     * Test that button component has consistent rounded-lg
     */
    public function test_button_component_has_consistent_rounded_lg(): void
    {
        $view = $this->blade('<x-ui.button>Test</x-ui.button>');
        
        $view->assertSee('rounded-lg');
    }
    
    /**
     * Test that button component has focus ring styling
     */
    public function test_button_component_has_focus_ring(): void
    {
        $view = $this->blade('<x-ui.button>Test</x-ui.button>');
        
        $view->assertSee('focus:outline-none');
        $view->assertSee('focus:ring-2');
        $view->assertSee('focus:ring-offset-2');
    }
}
