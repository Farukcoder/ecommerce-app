<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->word(),
            'slug' => fake()->unique()->slug(),
            'sku' => fake()->unique()->regexify('[A-Z0-9]{8}'),
            'base_price' => fake()->randomFloat(2, 10, 1000),
            'sale_price' => null,
            'description' => fake()->sentence(),
            'featured' => false,
            'status' => 'Published',
        ];
    }
}
