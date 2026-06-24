<?php

namespace Database\Factories;

use App\Models\Product;
use App\Models\ProductStock;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ProductStock>
 */
class ProductStockFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'product_id' => Product::factory(),
            'color_id' => null,
            'attribute_value_id' => null,
            'sku' => fake()->unique()->regexify('[A-Z0-9]{8}'),
            'quantity' => fake()->numberBetween(0, 100),
        ];
    }
}
