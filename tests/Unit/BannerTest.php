<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Banner;
use App\Services\BannerService;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class BannerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Create storage disk for testing
        Storage::fake('public');
    }

    /** @test */
    public function banner_model_exists_and_has_correct_fillable_fields()
    {
        $banner = new Banner();
        
        $expectedFillable = [
            'title',
            'image_path',
            'priority',
            'is_active',
            'created_by',
        ];
        
        $this->assertEquals($expectedFillable, $banner->getFillable());
    }

    /** @test */
    public function banner_model_has_correct_casts()
    {
        $banner = new Banner();
        
        $expectedCasts = [
            'id' => 'int',
            'priority' => 'integer',
            'is_active' => 'boolean',
            'created_by' => 'integer',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
        
        $this->assertEquals($expectedCasts, $banner->getCasts());
    }

    /** @test */
    public function banner_service_exists_and_has_required_methods()
    {
        // Get service from container (with dependency injection)
        $service = app(BannerService::class);
        
        $this->assertTrue(method_exists($service, 'store'));
        $this->assertTrue(method_exists($service, 'update'));
        $this->assertTrue(method_exists($service, 'delete'));
        $this->assertTrue(method_exists($service, 'toggleStatus'));
        $this->assertTrue(method_exists($service, 'processImage'));
        $this->assertTrue(method_exists($service, 'getActiveBanners'));
    }

    /** @test */
    public function banner_service_can_get_image_url()
    {
        // Get service from container (with dependency injection)
        $service = app(BannerService::class);
        
        // Test that getImageUrl method exists and handles null path
        $this->assertTrue(method_exists($service, 'getImageUrl'));
        $this->assertNull($service->getImageUrl(null));
        $this->assertNull($service->getImageUrl(''));
    }

    /** @test */
    public function banner_service_has_get_active_banners_method()
    {
        // Get service from container (with dependency injection)
        $service = app(BannerService::class);
        
        // Test that getActiveBanners returns a collection
        $this->assertTrue(method_exists($service, 'getActiveBanners'));
    }

    /** @test */
    public function image_url_accessor_returns_correct_format()
    {
        $banner = new Banner();
        $banner->image_path = 'banners/test-image.jpg';
        
        $expectedUrl = asset('storage/banners/test-image.jpg');
        $this->assertEquals($expectedUrl, $banner->image_url);
    }

    /** @test */
    public function image_url_accessor_returns_placeholder_when_no_path()
    {
        $banner = new Banner();
        $banner->image_path = null;
        
        $expectedUrl = asset('images/placeholder-banner.jpg');
        $this->assertEquals($expectedUrl, $banner->image_url);
    }

    /** @test */
    public function thumbnail_url_accessor_returns_correct_format()
    {
        $banner = new Banner();
        $banner->image_path = 'banners/test-image_1920.jpg';
        
        $expectedUrl = asset('storage/banners/test-image_1920_480.jpg');
        $this->assertEquals($expectedUrl, $banner->thumbnail_url);
    }

    /** @test */
    public function thumbnail_url_accessor_returns_placeholder_when_no_path()
    {
        $banner = new Banner();
        $banner->image_path = null;
        
        $expectedUrl = asset('images/placeholder-banner-thumb.jpg');
        $this->assertEquals($expectedUrl, $banner->thumbnail_url);
    }
}