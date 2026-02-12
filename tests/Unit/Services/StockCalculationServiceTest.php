<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\StockCalculationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockCalculationServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockCalculationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(StockCalculationService::class);
    }

    public function test_calculate_total_stock_value_simple_products()
    {
        // Product 1: Stock 10, Price 1000 = 10,000
        Product::factory()->create([
            'stock' => 10,
            'price' => 1000,
            'cost_price' => 500,
            'has_variants' => false,
        ]);

        // Product 2: Stock 5, Price 2000 = 10,000
        Product::factory()->create([
            'stock' => 5,
            'price' => 2000,
            'cost_price' => 1000,
            'has_variants' => false,
        ]);

        $stats = $this->service->calculateStockStats();

        $this->assertEquals(20000, $stats['total_value']);
        $this->assertEquals(10000, $stats['total_cost']);
        $this->assertEquals(10000, $stats['potential_profit']);
    }

    public function test_calculate_total_stock_value_with_variants()
    {
        // Parent Product (Price should be ignored for valuation)
        $product = Product::factory()->create([
            'price' => 5000, // Should be ignored
            'has_variants' => true,
        ]);

        // Variant 1: Stock 10, Price 1000 = 10,000
        ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Var A',
            'sku' => 'SKU-A',
            'stock' => 10,
            'price' => 1000,
            'cost_price' => 500,
            'is_active' => true,
            'option_values' => ['Size' => 'L'],
        ]);

        // Variant 2: Stock 5, Price 2000 = 10,000
        ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Var B',
            'sku' => 'SKU-B',
            'stock' => 5,
            'price' => 2000,
            'cost_price' => 1000,
            'is_active' => true,
            'option_values' => ['Size' => 'XL'],
        ]);

        // Sync logic usually happens in model events, but let's ensure it's fine.
        // The service should calculate based on variants directly.

        $stats = $this->service->calculateStockStats();

        $this->assertEquals(20000, $stats['total_value']);
        $this->assertEquals(10000, $stats['total_cost']);
    }

    public function test_mixed_products_valuation()
    {
        // Simple: 10 * 1000 = 10,000
        Product::factory()->create([
            'stock' => 10,
            'price' => 1000,
            'cost_price' => 500,
            'has_variants' => false,
        ]);

        // Variant Parent
        $product = Product::factory()->create([
            'has_variants' => true,
        ]);

        // Variant: 5 * 2000 = 10,000
        ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Var A',
            'sku' => 'SKU-A',
            'stock' => 5,
            'price' => 2000,
            'cost_price' => 1000,
            'is_active' => true,
            'option_values' => ['Size' => 'M'],
        ]);

        $stats = $this->service->calculateStockStats();

        $this->assertEquals(20000, $stats['total_value']);
    }

    public function test_low_stock_count()
    {
        // Normal
        Product::factory()->create(['stock' => 20, 'min_stock' => 10]);
        
        // Low
        Product::factory()->create(['stock' => 5, 'min_stock' => 10]);
        
        // Out
        Product::factory()->create(['stock' => 0, 'min_stock' => 10]);

        $stats = $this->service->calculateStockStats();

        $this->assertEquals(1, $stats['low_stock']);
        $this->assertEquals(1, $stats['out_of_stock']);
        $this->assertEquals(1, $stats['normal_stock']);
    }
}
