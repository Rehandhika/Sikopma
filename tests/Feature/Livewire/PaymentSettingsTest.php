<?php

namespace Tests\Feature\Livewire;

use App\Livewire\Settings\PaymentSettings;
use App\Models\Setting;
use App\Models\User;
use App\Services\PaymentConfigurationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Feature tests for PaymentSettings Livewire Component
 * 
 * Validates: Requirements 1.2, 1.3, 1.4, 2.1, 2.5, 3.1, 3.3, 7.1, 7.2
 */
class PaymentSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;
    protected User $regularUser;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create roles
        Role::create(['name' => 'Super Admin']);
        Role::create(['name' => 'Ketua']);
        Role::create(['name' => 'Wakil Ketua']);
        Role::create(['name' => 'Anggota']);

        // Create admin user
        $this->adminUser = User::factory()->create();
        $this->adminUser->assignRole('Super Admin');

        // Create regular user
        $this->regularUser = User::factory()->create();
        $this->regularUser->assignRole('Anggota');

        Cache::flush();
        Storage::fake('public');
    }

    /**
     * Test: Unauthorized user cannot access payment settings
     * Validates: Requirements 7.1, 7.2
     */
    public function test_unauthorized_user_cannot_access_payment_settings(): void
    {
        $this->actingAs($this->regularUser);

        $response = $this->get('/admin/pengaturan/pembayaran');

        $response->assertStatus(403);
    }

    /**
     * Test: Authorized user can access payment settings
     * Validates: Requirements 7.1
     */
    public function test_authorized_user_can_access_payment_settings(): void
    {
        $this->actingAs($this->adminUser);

        $response = $this->get('/admin/pengaturan/pembayaran');

        $response->assertStatus(200);
    }

    /**
     * Test: Component loads with default configuration
     * Validates: Requirements 1.2
     */
    public function test_component_loads_with_default_configuration(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->assertSet('cashEnabled', true)
            ->assertSet('transferEnabled', false)
            ->assertSet('qrisEnabled', false);
    }

    /**
     * Test: Component loads existing configuration
     * Validates: Requirements 1.2
     */
    public function test_component_loads_existing_configuration(): void
    {
        Setting::set(PaymentConfigurationService::KEY_CASH_ENABLED, '1');
        Setting::set(PaymentConfigurationService::KEY_TRANSFER_ENABLED, '1');
        Setting::set(PaymentConfigurationService::KEY_TRANSFER_BANK_NAME, 'BCA');
        Setting::set(PaymentConfigurationService::KEY_TRANSFER_ACCOUNT_NUMBER, '1234567890');
        Setting::set(PaymentConfigurationService::KEY_TRANSFER_ACCOUNT_HOLDER, 'Test Account');

        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->assertSet('cashEnabled', true)
            ->assertSet('transferEnabled', true)
            ->assertSet('bankName', 'BCA')
            ->assertSet('accountNumber', '1234567890')
            ->assertSet('accountHolder', 'Test Account');
    }

    /**
     * Test: Validation fails when all payment methods are disabled
     * Validates: Requirements 1.4
     */
    public function test_validation_fails_when_all_methods_disabled(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->set('cashEnabled', false)
            ->set('transferEnabled', false)
            ->set('qrisEnabled', false)
            ->call('save')
            ->assertHasErrors(['general']);
    }

    /**
     * Test: Validation fails when QRIS enabled without image
     * Validates: Requirements 2.1, 2.5
     */
    public function test_validation_fails_when_qris_enabled_without_image(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->set('cashEnabled', true)
            ->set('qrisEnabled', true)
            ->call('save')
            ->assertHasErrors(['qrisImage']);
    }

    /**
     * Test: Validation fails when transfer enabled without complete details
     * Validates: Requirements 3.1, 3.3
     */
    public function test_validation_fails_when_transfer_enabled_without_details(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->set('cashEnabled', true)
            ->set('transferEnabled', true)
            ->set('bankName', '')
            ->set('accountNumber', '')
            ->set('accountHolder', '')
            ->call('save')
            ->assertHasErrors(['bankName']);
    }

    /**
     * Test: Configuration saves successfully with valid data
     * Validates: Requirements 1.3
     */
    public function test_configuration_saves_successfully_with_valid_data(): void
    {
        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->set('cashEnabled', true)
            ->set('transferEnabled', true)
            ->set('bankName', 'BCA')
            ->set('accountNumber', '1234567890')
            ->set('accountHolder', 'Test Account')
            ->call('save')
            ->assertHasNoErrors()
            ->assertDispatched('alert');

        // Verify data was saved
        $this->assertEquals('1', Setting::get(PaymentConfigurationService::KEY_CASH_ENABLED));
        $this->assertEquals('1', Setting::get(PaymentConfigurationService::KEY_TRANSFER_ENABLED));
        $this->assertEquals('BCA', Setting::get(PaymentConfigurationService::KEY_TRANSFER_BANK_NAME));
    }

    /**
     * Test: QRIS image upload works correctly
     * Validates: Requirements 2.1, 2.2, 2.3
     */
    public function test_qris_image_upload_works_correctly(): void
    {
        $file = UploadedFile::fake()->image('qris.png', 200, 200);

        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->set('cashEnabled', true)
            ->set('qrisEnabled', true)
            ->set('qrisImage', $file)
            ->call('save')
            ->assertHasNoErrors();

        // Verify image was saved
        $qrisPath = Setting::get(PaymentConfigurationService::KEY_QRIS_IMAGE);
        $this->assertNotEmpty($qrisPath);
        Storage::disk('public')->assertExists($qrisPath);
    }

    /**
     * Test: Invalid image format is rejected
     * Validates: Requirements 2.2
     */
    public function test_invalid_image_format_is_rejected(): void
    {
        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->set('qrisImage', $file)
            ->assertHasErrors(['qrisImage']);
    }

    /**
     * Test: Image larger than 2MB is rejected
     * Validates: Requirements 2.3
     */
    public function test_image_larger_than_2mb_is_rejected(): void
    {
        $file = UploadedFile::fake()->image('large.png')->size(3000); // 3MB

        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->set('qrisImage', $file)
            ->assertHasErrors(['qrisImage']);
    }

    /**
     * Test: Remove QRIS image works correctly
     */
    public function test_remove_qris_image_works_correctly(): void
    {
        // First, set up an existing image
        Storage::disk('public')->put('payment/qris.png', 'fake-content');
        Setting::set(PaymentConfigurationService::KEY_QRIS_IMAGE, 'payment/qris.png');

        Livewire::actingAs($this->adminUser)
            ->test(PaymentSettings::class)
            ->call('removeQrisImage')
            ->assertSet('currentQrisImage', null);

        // Verify image was deleted
        Storage::disk('public')->assertMissing('payment/qris.png');
    }

    /**
     * Test: Ketua role can access payment settings
     * Validates: Requirements 7.1
     */
    public function test_ketua_role_can_access_payment_settings(): void
    {
        $ketuaUser = User::factory()->create();
        $ketuaUser->assignRole('Ketua');

        $this->actingAs($ketuaUser);

        $response = $this->get('/admin/pengaturan/pembayaran');

        $response->assertStatus(200);
    }

    /**
     * Test: Wakil Ketua role can access payment settings
     * Validates: Requirements 7.1
     */
    public function test_wakil_ketua_role_can_access_payment_settings(): void
    {
        $wakilKetuaUser = User::factory()->create();
        $wakilKetuaUser->assignRole('Wakil Ketua');

        $this->actingAs($wakilKetuaUser);

        $response = $this->get('/admin/pengaturan/pembayaran');

        $response->assertStatus(200);
    }

    protected function tearDown(): void
    {
        Cache::flush();
        parent::tearDown();
    }
}
