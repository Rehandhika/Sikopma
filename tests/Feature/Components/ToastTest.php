<?php

namespace Tests\Feature\Components;

use Tests\TestCase;

class ToastTest extends TestCase
{
    /**
     * Test that toast component renders without errors
     */
    public function test_toast_component_renders(): void
    {
        $view = $this->blade('<x-ui.toast />');
        
        $view->assertSee('role="region"', false);
        $view->assertSee('aria-live="polite"', false);
        $view->assertSee('aria-label="Notifications"', false);
    }
    
    /**
     * Test that toast component accepts position prop
     */
    public function test_toast_component_accepts_position_prop(): void
    {
        $view = $this->blade('<x-ui.toast position="top-left" />');
        
        $view->assertSee('top-4 left-4');
    }
    
    /**
     * Test that toast component accepts duration prop
     */
    public function test_toast_component_accepts_duration_prop(): void
    {
        $view = $this->blade('<x-ui.toast :duration="5000" />');
        
        $view->assertSee('defaultDuration: 5000');
    }
    
    /**
     * Test that toast component accepts maxToasts prop
     */
    public function test_toast_component_accepts_max_toasts_prop(): void
    {
        $view = $this->blade('<x-ui.toast :maxToasts="10" />');
        
        $view->assertSee('maxToasts: 10');
    }
    
    /**
     * Test that toast component has all variant styles
     */
    public function test_toast_component_has_all_variant_styles(): void
    {
        $view = $this->blade('<x-ui.toast />');
        
        // Success variant
        $view->assertSee('bg-emerald-50');
        $view->assertSee('dark:bg-emerald-900/30');
        $view->assertSee('border-emerald-200');
        $view->assertSee('text-emerald-800');
        
        // Error variant
        $view->assertSee('bg-red-50');
        $view->assertSee('dark:bg-red-900/30');
        $view->assertSee('border-red-200');
        $view->assertSee('text-red-800');
        
        // Warning variant
        $view->assertSee('bg-amber-50');
        $view->assertSee('dark:bg-amber-900/30');
        $view->assertSee('border-amber-200');
        $view->assertSee('text-amber-800');
        
        // Info variant
        $view->assertSee('bg-blue-50');
        $view->assertSee('dark:bg-blue-900/30');
        $view->assertSee('border-blue-200');
        $view->assertSee('text-blue-800');
    }
    
    /**
     * Test that toast component has Alpine.js store initialization
     */
    public function test_toast_component_has_alpine_store_initialization(): void
    {
        $view = $this->blade('<x-ui.toast />');
        
        $view->assertSee("Alpine.store('toasts')", false);
        $view->assertSee('items: []');
        $view->assertSee('add(message, type =', false);
        $view->assertSee('remove(id)');
    }
    
    /**
     * Test that toast component listens to toast events
     */
    public function test_toast_component_listens_to_events(): void
    {
        $view = $this->blade('<x-ui.toast />');
        
        $view->assertSee("window.addEventListener('toast'", false);
        $view->assertSee("window.addEventListener('alert'", false);
        $view->assertSee("window.addEventListener('show-access-denied'", false);
    }
}
