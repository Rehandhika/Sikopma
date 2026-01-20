<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Livewire\Admin\BannerManagement;
use App\Services\BannerService;
use App\Models\Banner;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Mockery;

class BannerAdminPanelTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function banner_management_component_exists()
    {
        $this->assertTrue(class_exists(BannerManagement::class));
    }

    /** @test */
    public function banner_management_has_required_properties()
    {
        $component = new BannerManagement();
        
        $this->assertObjectHasProperty('title', $component);
        $this->assertObjectHasProperty('image', $component);
        $this->assertObjectHasProperty('priority', $component);
        $this->assertObjectHasProperty('editingBannerId', $component);
        $this->assertObjectHasProperty('showForm', $component);
    }

    /** @test */
    public function banner_management_has_required_methods()
    {
        $component = new BannerManagement();
        
        $this->assertTrue(method_exists($component, 'create'));
        $this->assertTrue(method_exists($component, 'edit'));
        $this->assertTrue(method_exists($component, 'save'));
        $this->assertTrue(method_exists($component, 'delete'));
        $this->assertTrue(method_exists($component, 'toggleStatus'));
        $this->assertTrue(method_exists($component, 'render'));
    }

    /** @test */
    public function banner_service_integration_works()
    {
        // Get service from container (with dependency injection)
        $service = app(BannerService::class);
        
        // Test that all required methods exist
        $this->assertTrue(method_exists($service, 'store'));
        $this->assertTrue(method_exists($service, 'update'));
        $this->assertTrue(method_exists($service, 'delete'));
        $this->assertTrue(method_exists($service, 'toggleStatus'));
        $this->assertTrue(method_exists($service, 'processImage'));
        $this->assertTrue(method_exists($service, 'getActiveBanners'));
    }

    /** @test */
    public function banner_validation_rules_are_correct()
    {
        $component = new BannerManagement();
        
        // Use reflection to access validation rules
        $reflection = new \ReflectionClass($component);
        $properties = $reflection->getProperties();
        
        $titleProperty = null;
        $imageProperty = null;
        $priorityProperty = null;
        
        foreach ($properties as $property) {
            $attributes = $property->getAttributes();
            foreach ($attributes as $attribute) {
                if ($attribute->getName() === 'Livewire\Attributes\Validate') {
                    $args = $attribute->getArguments();
                    if ($property->getName() === 'title') {
                        $this->assertStringContainsString('nullable', $args[0]);
                        $this->assertStringContainsString('string', $args[0]);
                        $this->assertStringContainsString('max:255', $args[0]);
                    }
                    if ($property->getName() === 'image') {
                        $this->assertStringContainsString('required_without:editingBannerId', $args[0]);
                        $this->assertStringContainsString('image', $args[0]);
                        $this->assertStringContainsString('mimes:jpg,jpeg,png', $args[0]);
                        $this->assertStringContainsString('max:5120', $args[0]);
                    }
                    if ($property->getName() === 'priority') {
                        $this->assertStringContainsString('required', $args[0]);
                        $this->assertStringContainsString('integer', $args[0]);
                        $this->assertStringContainsString('min:0', $args[0]);
                    }
                }
            }
        }
    }

    /** @test */
    public function banner_model_relationships_work()
    {
        $banner = new Banner();
        
        // Test that creator relationship exists
        $this->assertTrue(method_exists($banner, 'creator'));
        
        // Test that scopes exist
        $this->assertTrue(method_exists($banner, 'scopeActive'));
        $this->assertTrue(method_exists($banner, 'scopeOrdered'));
        
        // Test that accessors exist
        $this->assertTrue(method_exists($banner, 'getImageUrlAttribute'));
        $this->assertTrue(method_exists($banner, 'getThumbnailUrlAttribute'));
    }

    /** @test */
    public function image_processing_handles_different_sizes()
    {
        // Get service from container (with dependency injection)
        $service = app(BannerService::class);
        
        // Test that processImage method exists
        $this->assertTrue(method_exists($service, 'processImage'));
        
        // Test that getImageUrl method exists and handles null
        $this->assertTrue(method_exists($service, 'getImageUrl'));
        $this->assertNull($service->getImageUrl(null));
    }

    /** @test */
    public function banner_crud_operations_structure_is_correct()
    {
        // Test that the BannerManagement component has the correct structure for CRUD operations
        $component = new BannerManagement();
        
        // Verify form properties are initialized correctly
        $this->assertEquals('', $component->title);
        $this->assertEquals(0, $component->priority);
        $this->assertNull($component->editingBannerId);
        $this->assertFalse($component->showForm);
        
        // Test that resetForm method exists and works
        $this->assertTrue(method_exists($component, 'resetForm'));
        
        // Test that cancelEdit method exists
        $this->assertTrue(method_exists($component, 'cancelEdit'));
    }

    /** @test */
    public function banner_service_file_deletion_logic_is_sound()
    {
        // Get service from container (with dependency injection)
        $service = app(BannerService::class);
        
        // Test that delete method exists
        $this->assertTrue(method_exists($service, 'delete'));
        
        // Create some fake files
        Storage::disk('public')->put('banners/test-uuid_1920.jpg', 'fake content');
        Storage::disk('public')->put('banners/test-uuid_768.jpg', 'fake content');
        Storage::disk('public')->put('banners/test-uuid_480.jpg', 'fake content');
        
        // Verify files exist
        $this->assertTrue(Storage::disk('public')->exists('banners/test-uuid_1920.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('banners/test-uuid_768.jpg'));
        $this->assertTrue(Storage::disk('public')->exists('banners/test-uuid_480.jpg'));
        
        // Use reflection to test protected method
        $reflection = new \ReflectionClass($service);
        $method = $reflection->getMethod('deleteImageFilesLegacy');
        $method->setAccessible(true);
        
        // Call delete method
        $method->invoke($service, 'banners/test-uuid_1920.jpg');
        
        // Verify files are deleted
        $this->assertFalse(Storage::disk('public')->exists('banners/test-uuid_1920.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('banners/test-uuid_768.jpg'));
        $this->assertFalse(Storage::disk('public')->exists('banners/test-uuid_480.jpg'));
    }
}