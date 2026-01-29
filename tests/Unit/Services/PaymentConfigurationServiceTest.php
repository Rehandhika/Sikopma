<?php

namespace Tests\Unit\Services;

use App\Models\Setting;
use App\Services\PaymentConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

/**
 * Unit tests for PaymentConfigurationService
 * 
 * Validates: Requirements 1.1, 1.3, 1.4, 6.1, 6.2
 */
class PaymentConfigurationServiceTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentConfigurationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new PaymentConfigurationService();
        Cache::flush();
        Storage::fake('public');
    }

    /**
     * Test: Service can be instantiated
     */
    public function test_service_can_be_instantiated(): void
    {
        $this->assertInstanceOf(PaymentConfigurationService::class, $this->service);
    }

    /**
     * Test: getAll returns correct default structure
     * Validates: Requirements 1.1
     */
    public function test_get_all_returns_correct_default_structure(): void
    {
        $config = $this->service->getAll();

        $this->assertIsArray($config);
        $this->assertArrayHasKey('cash_enabled', $config);
        $this->assertArrayHasKey('transfer_enabled', $config);
        $this->assertArrayHasKey('qris_enabled', $config);
        $this->assertArrayHasKey('transfer_bank_name', $config);
        $this->assertArrayHasKey('transfer_account_number', $config);
        $this->assertArrayHasKey('transfer_account_holder', $config);
        $this->assertArrayHasKey('qris_image', $config);
    }

    /**
     * Test: Cash is enabled by default
     * Validates: Requirements 4.1
     */
    public function test_cash_is_enabled_by_default(): void
    {
        $config = $this->service->getAll();

        $this->assertTrue($config['cash_enabled']);
    }

    /**
     * Test: Transfer and QRIS are disabled by default
     * Validates: Requirements 4.1
     */
    public function test_transfer_and_qris_disabled_by_default(): void
    {
        $config = $this->service->getAll();

        $this->assertFalse($config['transfer_enabled']);
        $this->assertFalse($config['qris_enabled']);
    }

    /**
     * Test: getEnabledMethods returns only enabled methods
     * Validates: Requirements 5.1
     */
    public function test_get_enabled_methods_returns_only_enabled(): void
    {
        // Default: only cash enabled
        $methods = $this->service->getEnabledMethods();

        $this->assertCount(1, $methods);
        $this->assertEquals('cash', $methods[0]['id']);
    }

    /**
     * Test: getEnabledMethods returns all enabled methods
     * Validates: Requirements 5.1
     */
    public function test_get_enabled_methods_returns_all_enabled(): void
    {
        // Enable all methods
        Setting::set(PaymentConfigurationService::KEY_CASH_ENABLED, '1');
        Setting::set(PaymentConfigurationService::KEY_TRANSFER_ENABLED, '1');
        Setting::set(PaymentConfigurationService::KEY_QRIS_ENABLED, '1');
        $this->service->clearCache();

        $methods = $this->service->getEnabledMethods();

        $this->assertCount(3, $methods);
        $methodIds = array_column($methods, 'id');
        $this->assertContains('cash', $methodIds);
        $this->assertContains('transfer', $methodIds);
        $this->assertContains('qris', $methodIds);
    }

    /**
     * Test: isMethodEnabled returns correct status
     * Validates: Requirements 1.1
     */
    public function test_is_method_enabled_returns_correct_status(): void
    {
        $this->assertTrue($this->service->isMethodEnabled('cash'));
        $this->assertFalse($this->service->isMethodEnabled('transfer'));
        $this->assertFalse($this->service->isMethodEnabled('qris'));
        $this->assertFalse($this->service->isMethodEnabled('invalid'));
    }

    /**
     * Test: saveConfiguration persists data correctly
     * Validates: Requirements 1.3
     */
    public function test_save_configuration_persists_data(): void
    {
        $data = [
            'cash_enabled' => true,
            'transfer_enabled' => true,
            'qris_enabled' => false,
            'transfer_bank_name' => 'BCA',
            'transfer_account_number' => '1234567890',
            'transfer_account_holder' => 'Test Account',
        ];

        $this->service->saveConfiguration($data);

        $config = $this->service->getAll();
        $this->assertTrue($config['cash_enabled']);
        $this->assertTrue($config['transfer_enabled']);
        $this->assertFalse($config['qris_enabled']);
        $this->assertEquals('BCA', $config['transfer_bank_name']);
        $this->assertEquals('1234567890', $config['transfer_account_number']);
        $this->assertEquals('Test Account', $config['transfer_account_holder']);
    }

    /**
     * Test: Cache is invalidated after save
     * Validates: Requirements 6.1, 6.2
     */
    public function test_cache_is_invalidated_after_save(): void
    {
        // First call - should cache
        $config1 = $this->service->getAll();
        $this->assertTrue($config1['cash_enabled']);

        // Save new configuration
        $this->service->saveConfiguration(['cash_enabled' => false, 'transfer_enabled' => true]);

        // Get again - should return new values (cache invalidated)
        $config2 = $this->service->getAll();
        $this->assertFalse($config2['cash_enabled']);
        $this->assertTrue($config2['transfer_enabled']);
    }

    /**
     * Test: getTransferDetails returns null when transfer disabled
     * Validates: Requirements 3.1
     */
    public function test_get_transfer_details_returns_null_when_disabled(): void
    {
        $details = $this->service->getTransferDetails();
        $this->assertNull($details);
    }

    /**
     * Test: getTransferDetails returns details when enabled and complete
     * Validates: Requirements 3.2, 3.4
     */
    public function test_get_transfer_details_returns_details_when_enabled(): void
    {
        Setting::set(PaymentConfigurationService::KEY_TRANSFER_ENABLED, '1');
        Setting::set(PaymentConfigurationService::KEY_TRANSFER_BANK_NAME, 'BCA');
        Setting::set(PaymentConfigurationService::KEY_TRANSFER_ACCOUNT_NUMBER, '1234567890');
        Setting::set(PaymentConfigurationService::KEY_TRANSFER_ACCOUNT_HOLDER, 'Test Account');
        $this->service->clearCache();

        $details = $this->service->getTransferDetails();

        $this->assertNotNull($details);
        $this->assertEquals('BCA', $details['bank_name']);
        $this->assertEquals('1234567890', $details['account_number']);
        $this->assertEquals('Test Account', $details['account_holder']);
    }

    /**
     * Test: getQrisImageUrl returns null when QRIS disabled
     * Validates: Requirements 2.4
     */
    public function test_get_qris_image_url_returns_null_when_disabled(): void
    {
        $url = $this->service->getQrisImageUrl();
        $this->assertNull($url);
    }

    /**
     * Test: getQrisImageUrl returns URL when enabled and image exists
     * Validates: Requirements 2.4
     */
    public function test_get_qris_image_url_returns_url_when_enabled(): void
    {
        // Create a fake image file
        Storage::disk('public')->put('payment/qris.png', 'fake-image-content');

        Setting::set(PaymentConfigurationService::KEY_QRIS_ENABLED, '1');
        Setting::set(PaymentConfigurationService::KEY_QRIS_IMAGE, 'payment/qris.png');
        $this->service->clearCache();

        $url = $this->service->getQrisImageUrl();

        $this->assertNotNull($url);
        $this->assertStringContainsString('payment/qris.png', $url);
    }

    /**
     * Test: clearCache removes cached configuration
     * Validates: Requirements 6.2
     */
    public function test_clear_cache_removes_cached_configuration(): void
    {
        // Populate cache
        $this->service->getAll();
        $this->assertTrue(Cache::has(PaymentConfigurationService::CACHE_KEY));

        // Clear cache
        $this->service->clearCache();

        $this->assertFalse(Cache::has(PaymentConfigurationService::CACHE_KEY));
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
