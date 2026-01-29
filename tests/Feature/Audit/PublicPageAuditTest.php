<?php

namespace Tests\Feature\Audit;

use App\Models\Product;
use App\Models\StoreSetting;
use Livewire\Livewire;

/**
 * Public Page Audit Tests
 *
 * Tests all public-facing pages load correctly and function as expected.
 * Validates Requirements: 1.1, 1.2, 1.3, 1.4
 */
class PublicPageAuditTest extends AuditTestCase
{
    /**
     * Test home page (/) loads with catalog.
     *
     * Requirement 1.1: WHEN a visitor accesses the home page (/)
     * THEN the System SHALL display the public catalog with product listings
     */
    public function test_home_page_loads_with_catalog(): void
    {
        // Seed some public products
        $publicProducts = $this->seedProducts(3, true);

        // Clear cache to ensure fresh data
        \Illuminate\Support\Facades\Cache::flush();

        $response = $this->get('/');

        $response->assertStatus(200);
        // Verify the page contains Livewire component markers
        $response->assertSee('wire:');
    }

    /**
     * Test home page displays public products.
     *
     * Requirement 1.2: WHEN a visitor accesses the products page (/products)
     * THEN the System SHALL display all products marked as public
     */
    public function test_home_page_displays_public_products(): void
    {
        // Create public products
        $publicProduct = $this->seedProduct([
            'name' => 'Public Test Product',
            'is_public' => true,
            'status' => 'active',
        ]);

        // Create non-public product
        $privateProduct = $this->seedProduct([
            'name' => 'Private Test Product',
            'is_public' => false,
            'status' => 'active',
        ]);

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee($publicProduct->name);
        $response->assertDontSee($privateProduct->name);
    }

    /**
     * Test products page (/products) displays public products.
     *
     * Requirement 1.2: WHEN a visitor accesses the products page (/products)
     * THEN the System SHALL display all products marked as public with correct images, prices, and descriptions
     */
    public function test_products_page_displays_public_products(): void
    {
        // Create public products with specific attributes
        $publicProduct = $this->seedProduct([
            'name' => 'Visible Product',
            'description' => 'This is a visible product description',
            'price' => 25000,
            'is_public' => true,
            'status' => 'active',
        ]);

        // Create non-public product
        $privateProduct = $this->seedProduct([
            'name' => 'Hidden Product',
            'is_public' => false,
            'status' => 'active',
        ]);

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee($publicProduct->name);
        $response->assertDontSee($privateProduct->name);
    }

    /**
     * Test products page only shows active products.
     *
     * Requirement 1.2: Products should be active to be displayed
     */
    public function test_products_page_only_shows_active_products(): void
    {
        // Create active public product
        $activeProduct = $this->seedProduct([
            'name' => 'Active Product',
            'is_public' => true,
            'status' => 'active',
        ]);

        // Create inactive public product
        $inactiveProduct = $this->seedProduct([
            'name' => 'Inactive Product',
            'is_public' => true,
            'status' => 'inactive',
        ]);

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();

        $response = $this->get('/products');

        $response->assertStatus(200);
        $response->assertSee($activeProduct->name);
        $response->assertDontSee($inactiveProduct->name);
    }

    /**
     * Test product detail page (/products/{slug}) shows product info.
     *
     * Requirement 1.3: WHEN a visitor accesses a product detail page (/products/{slug})
     * THEN the System SHALL display complete product information including name, description, price, and availability status
     */
    public function test_product_detail_page_shows_product_info(): void
    {
        $product = $this->seedProduct([
            'name' => 'Detail Test Product',
            'slug' => 'detail-test-product',
            'description' => 'This is a detailed product description for testing',
            'price' => 50000,
            'stock' => 100,
            'is_public' => true,
            'status' => 'active',
        ]);

        $response = $this->get('/products/'.$product->slug);

        $response->assertStatus(200);
        $response->assertSee($product->name);
        $response->assertSee($product->description);
    }

    /**
     * Test product detail page returns 404 for non-existent product.
     *
     * Requirement 1.5: WHEN a visitor accesses a non-existent public page
     * THEN the System SHALL display a user-friendly 404 error page
     */
    public function test_product_detail_returns_404_for_nonexistent_product(): void
    {
        $response = $this->get('/products/nonexistent-product-slug');

        $response->assertStatus(404);
    }

    /**
     * Test product detail page returns 404 for private product.
     *
     * Private products should not be accessible via public URL
     */
    public function test_product_detail_returns_404_for_private_product(): void
    {
        $privateProduct = $this->seedProduct([
            'name' => 'Private Product',
            'slug' => 'private-product',
            'is_public' => false,
            'status' => 'active',
        ]);

        $response = $this->get('/products/'.$privateProduct->slug);

        $response->assertStatus(404);
    }

    /**
     * Test about page (/about) renders correctly.
     *
     * Requirement 1.4: WHEN a visitor accesses the about page (/about)
     * THEN the System SHALL display cooperative information with proper layout and no broken assets
     */
    public function test_about_page_renders_correctly(): void
    {
        // Seed store settings
        StoreSetting::create([
            'contact_phone' => '08123456789',
            'contact_email' => 'kopma@test.com',
            'contact_whatsapp' => '08123456789',
            'contact_address' => 'Jl. Test No. 123',
            'about_text' => 'This is the about text for KOPMA testing.',
            'operating_hours' => [
                'monday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                'tuesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                'wednesday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                'thursday' => ['open' => '07:30', 'close' => '16:00', 'is_open' => true],
                'friday' => ['open' => null, 'close' => null, 'is_open' => false],
                'saturday' => ['open' => null, 'close' => null, 'is_open' => false],
                'sunday' => ['open' => null, 'close' => null, 'is_open' => false],
            ],
        ]);

        $response = $this->get('/about');

        $response->assertStatus(200);
        // Verify the page contains expected content
        $response->assertSee('kopma@test.com');
    }

    /**
     * Test about page renders with default settings when no store settings exist.
     */
    public function test_about_page_renders_with_default_settings(): void
    {
        // Don't seed any store settings - component should handle this gracefully

        $response = $this->get('/about');

        $response->assertStatus(200);
    }

    /**
     * Test public pages are accessible without authentication.
     *
     * All public pages should be accessible to guests
     */
    public function test_public_pages_accessible_without_authentication(): void
    {
        // Seed a product for product detail test
        $product = $this->seedProduct([
            'slug' => 'guest-test-product',
            'is_public' => true,
            'status' => 'active',
        ]);

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();

        // Test all public routes as guest
        $this->get('/')->assertStatus(200);
        $this->get('/products')->assertStatus(200);
        $this->get('/products/'.$product->slug)->assertStatus(200);
        $this->get('/about')->assertStatus(200);
    }

    /**
     * Test public pages are also accessible when authenticated.
     *
     * Authenticated users should still be able to access public pages
     */
    public function test_public_pages_accessible_when_authenticated(): void
    {
        // Seed a product for product detail test
        $product = $this->seedProduct([
            'slug' => 'auth-test-product',
            'is_public' => true,
            'status' => 'active',
        ]);

        // Clear cache
        \Illuminate\Support\Facades\Cache::flush();

        // Test all public routes as authenticated user
        $this->actingAs($this->anggota);

        $this->get('/')->assertStatus(200);
        $this->get('/products')->assertStatus(200);
        $this->get('/products/'.$product->slug)->assertStatus(200);
        $this->get('/about')->assertStatus(200);
    }
}
