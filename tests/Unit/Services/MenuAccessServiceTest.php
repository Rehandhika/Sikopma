<?php

namespace Tests\Unit\Services;

use App\Services\MenuAccessService;
use Mockery;
use Tests\TestCase;

/**
 * Unit tests for MenuAccessService
 *
 * Validates: Requirements 5.1, 5.2, 5.3, 5.4
 */
class MenuAccessServiceTest extends TestCase
{
    protected MenuAccessService $menuAccessService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->menuAccessService = new MenuAccessService;
    }

    /**
     * Test: Service can be instantiated
     */
    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(MenuAccessService::class, $this->menuAccessService);
    }

    /**
     * Test: Unauthenticated user gets all menus locked
     */
    public function test_unauthenticated_user_gets_all_menus_locked(): void
    {
        // No user authenticated
        $menuItems = $this->menuAccessService->getMenuWithAccessState();

        foreach ($menuItems as $item) {
            if (isset($item['type']) && $item['type'] === 'divider') {
                continue;
            }

            $this->assertFalse(
                $item['accessible'],
                "Menu '{$item['label']}' should be locked for unauthenticated user"
            );
        }
    }

    /**
     * Test: canAccess returns false for unauthenticated user
     */
    public function test_can_access_returns_false_for_unauthenticated_user(): void
    {
        $this->assertFalse($this->menuAccessService->canAccess('dashboard'));
        $this->assertFalse($this->menuAccessService->canAccess('users'));
    }

    /**
     * Test: canAccess returns false for non-existent menu key
     */
    public function test_can_access_returns_false_for_nonexistent_menu(): void
    {
        // Create a mock user
        $user = Mockery::mock(\App\Models\User::class);
        $user->shouldReceive('hasRole')->with('Super Admin')->andReturn(false);

        $this->actingAs($user);

        $this->assertFalse($this->menuAccessService->canAccess('nonexistent_menu'));
    }

    /**
     * Test: Menu configuration is loaded correctly
     */
    public function test_menu_configuration_is_loaded(): void
    {
        $menuConfig = config('menu.items');

        $this->assertIsArray($menuConfig);
        $this->assertNotEmpty($menuConfig);

        // Check that dashboard menu exists
        $dashboardMenu = collect($menuConfig)->firstWhere('key', 'dashboard');
        $this->assertNotNull($dashboardMenu);
        $this->assertEquals('Dashboard', $dashboardMenu['label']);
    }

    /**
     * Test: Super admin role is configured
     */
    public function test_super_admin_role_is_configured(): void
    {
        $superAdminRole = config('menu.super_admin_role');

        $this->assertEquals('Super Admin', $superAdminRole);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }
}
