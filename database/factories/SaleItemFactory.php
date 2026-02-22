<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SaleItem>
 */
class SaleItemFactory extends Factory
{
    protected $model = SaleItem::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $product = Product::factory()->createOne();
        $quantity = fake()->numberBetween(1, 10);
        $price = fake()->randomFloat(2, 5000, 100000);

        return [
            'sale_id' => Sale::factory(),
            'product_id' => $product->id,
            'variant_id' => null,
            'product_name' => $product->name,
            'quantity' => $quantity,
            'price' => $price,
            'subtotal' => $quantity * $price,
        ];
    }

    /**
     * Indicate that the sale item has a variant.
     */
    public function withVariant(): static
    {
        return $this->afterMaking(function (SaleItem $item) {
            if ($item->product_id) {
                $variant = \App\Models\ProductVariant::factory()->createOne([
                    'product_id' => $item->product_id,
                ]);
                $item->variant_id = $variant->id;
            }
        });
    }

    /**
     * Indicate that the sale item is for a specific sale.
     */
    public function forSale(Sale $sale): static
    {
        return $this->state(fn (array $attributes) => [
            'sale_id' => $sale->id,
        ]);
    }

    /**
     * Indicate that the sale item is for a specific product.
     */
    public function forProduct(Product $product): static
    {
        return $this->state(fn (array $attributes) => [
            'product_id' => $product->id,
            'product_name' => $product->name,
        ]);
    }

    /**
     * Indicate that the quantity is bulk (more than 5).
     */
    public function bulk(): static
    {
        return $this->state(fn (array $attributes) => [
            'quantity' => fake()->numberBetween(6, 50),
        ]);
    }
}
