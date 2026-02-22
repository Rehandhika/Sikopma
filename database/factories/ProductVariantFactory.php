<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ProductVariant>
 */
class ProductVariantFactory extends Factory
{
    protected $model = ProductVariant::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'sku' => strtoupper(fake()->unique()->bothify('???-####')),
            'variant_name' => fake()->randomElement(['Small', 'Medium', 'Large', 'XL', 'XXL']),
            'price' => fake()->randomFloat(2, 5000, 500000),
            'cost_price' => fake()->randomFloat(2, 3000, 300000),
            'stock' => fake()->numberBetween(0, 100),
            'min_stock' => fake()->numberBetween(5, 20),
            'option_values' => ['size' => 'M', 'color' => 'Red'],
            'is_active' => true,
        ];
    }

    /**
     * Indicate that the variant is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the variant is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the variant is out of stock.
     */
    public function outOfStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => 0,
        ]);
    }

    /**
     * Indicate that the variant is low stock.
     */
    public function lowStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => fake()->numberBetween(1, 5),
        ]);
    }

    /**
     * Indicate that the variant has high stock.
     */
    public function highStock(): static
    {
        return $this->state(fn (array $attributes) => [
            'stock' => fake()->numberBetween(50, 200),
        ]);
    }
}
