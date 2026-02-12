<?php

namespace Tests\Feature\Livewire\Stock;

use App\Livewire\Stock\ProcurementModal;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class ProcurementTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_procurement_and_update_stock_cost()
    {
        $user = User::factory()->create();
        
        // Initial: Stock 10, Cost 5000
        $product = Product::factory()->create([
            'stock' => 10,
            'cost_price' => 5000,
            'price' => 10000,
        ]);

        Livewire::actingAs($user)
            ->test(ProcurementModal::class)
            ->call('open')
            ->set('supplier_name', 'Toko ABC')
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'variant_id' => null,
                    'name' => $product->name,
                    'sku' => $product->sku, // Add SKU
                    'quantity' => 10,
                    'cost_price' => 6000,
                    'subtotal' => 60000
                ]
            ])
            ->call('save');

        $product->refresh();

        // Stock should be 10 + 10 = 20
        $this->assertEquals(20, $product->stock);

        // Cost should be weighted average:
        // ((10 * 5000) + (10 * 6000)) / 20 = (50000 + 60000) / 20 = 5500
        $this->assertEquals(5500, $product->cost_price);
    }

    public function test_variant_procurement_updates_parent_stock()
    {
        $user = User::factory()->create();
        
        $product = Product::factory()->create(['has_variants' => true]);
        
        $variant = ProductVariant::create([
            'product_id' => $product->id,
            'variant_name' => 'Var A',
            'sku' => 'SKU-A',
            'stock' => 5,
            'price' => 2000,
            'cost_price' => 1000,
            'is_active' => true,
            'option_values' => ['size' => 'L'], // Add required field
        ]);

        Livewire::actingAs($user)
            ->test(ProcurementModal::class)
            ->call('open')
            ->set('items', [
                [
                    'product_id' => $product->id,
                    'variant_id' => $variant->id,
                    'name' => 'Var A',
                    'sku' => 'SKU-A', // Add SKU
                    'quantity' => 5,
                    'cost_price' => 2000,
                    'subtotal' => 10000
                ]
            ])
            ->call('save');

        $variant->refresh();
        $product->refresh();

        // Variant Stock: 5 + 5 = 10
        $this->assertEquals(10, $variant->stock);
        
        // Parent Stock: 10
        $this->assertEquals(10, $product->stock);

        // Avg Cost: ((5 * 1000) + (5 * 2000)) / 10 = 1500
        $this->assertEquals(1500, $variant->cost_price);
    }
}
