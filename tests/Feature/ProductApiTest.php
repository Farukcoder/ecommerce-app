<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_products_endpoint_returns_published_products(): void
    {
        $brand = Brand::create([
            'name' => 'Aurora',
            'slug' => 'aurora',
            'status' => true,
        ]);

        $category = Category::create([
            'name' => 'Accessories',
            'slug' => 'accessories',
            'status' => true,
        ]);

        $product = Product::create([
            'brand_id' => $brand->id,
            'name' => 'Leather Tote Bag',
            'slug' => 'leather-tote-bag',
            'sku' => 'TOT-001',
            'base_price' => 2500,
            'sale_price' => 1999,
            'featured' => true,
            'status' => 'Published',
        ]);

        $product->categories()->attach($category->id);

        Product::create([
            'name' => 'Hidden Draft Product',
            'slug' => 'hidden-draft-product',
            'base_price' => 1000,
            'status' => 'Draft',
        ]);

        $response = $this->getJson('/api/home/products');

        $response->assertOk()
            ->assertJsonPath('data.0.name', 'Leather Tote Bag')
            ->assertJsonPath('data.0.price', 1999)
            ->assertJsonPath('data.0.originalPrice', 2500)
            ->assertJsonPath('data.0.badge', 'bestseller')
            ->assertJsonPath('data.0.category', 'accessories');

        $this->assertDatabaseHas('products', [
            'slug' => 'hidden-draft-product',
        ]);
    }
}
