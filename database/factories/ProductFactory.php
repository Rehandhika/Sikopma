<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $name = $this->faker->unique()->words(3, true);
        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'sku' => $this->faker->unique()->bothify('PROD-####'),
            'price' => $this->faker->numberBetween(10000, 100000),
            'cost_price' => $this->faker->numberBetween(5000, 9000),
            'stock' => $this->faker->numberBetween(0, 100),
            'min_stock' => 10,
            'category' => $this->faker->word,
            'description' => $this->faker->sentence,
            'status' => 'active',
            'has_variants' => false,
            'is_public' => true,
            'is_featured' => false,
            'display_order' => 0,
        ];
    }
}
