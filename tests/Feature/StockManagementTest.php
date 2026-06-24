<?php

namespace Tests\Feature;

use App\Models\Attribute;
use App\Models\AttributeValue;
use App\Models\Color;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_stock_details_returns_existing_variants(): void
    {
        $product = Product::factory()->create();
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        ProductStock::factory()->create([
            'product_id' => $product->id,
            'attribute_value_id' => $attributeValue->id,
            'sku' => 'TEST-SKU-001',
            'quantity' => 10,
        ]);

        $response = $this->getJson(route('products.stock.details', $product->uuid));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'product' => [
                    'id' => $product->id,
                    'name' => $product->name,
                ],
            ])
            ->assertJsonCount(1, 'variants');
    }

    public function test_create_new_stock_endpoint(): void
    {
        $product = Product::factory()->create(['base_price' => 100]);
        $color = Color::factory()->create();
        $attribute = Attribute::factory()->create();
        $attributeValue = AttributeValue::factory()->create(['attribute_id' => $attribute->id]);

        $response = $this->postJson(route('products.stock.create'), [
            'product_id' => $product->id,
            'color_id' => $color->id,
            'attribute_value_id' => $attributeValue->id,
            'sku' => 'NEW-SKU-001',
            'quantity' => 50,
            'price' => 100,
            'date' => now()->toDateString(),
            'note' => 'Test stock addition',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => __('messages.stock_added_successfully'),
            ]);

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'sku' => 'NEW-SKU-001',
            'quantity' => 50,
        ]);

        $this->assertDatabaseHas('stock_logs', [
            'product_id' => $product->id,
            'quantity' => 50,
            'change_type' => 'addition',
        ]);
    }

    public function test_api_colors_endpoint(): void
    {
        Color::factory()->create(['name' => 'Red']);
        Color::factory()->create(['name' => 'Blue']);

        $response = $this->getJson('/api/home/colors');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'colors' => [
                    '*' => ['id', 'name'],
                ],
            ])
            ->assertJsonCount(2, 'colors');
    }

    public function test_api_attributes_endpoint(): void
    {
        $attribute = Attribute::factory()->create(['name' => 'Size']);
        AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'value' => 'Small']);
        AttributeValue::factory()->create(['attribute_id' => $attribute->id, 'value' => 'Large']);

        $response = $this->getJson('/api/home/attributes');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'attributes' => [
                    '*' => ['id', 'attribute_id', 'value', 'attribute'],
                ],
            ])
            ->assertJsonCount(2, 'attributes');
    }

    public function test_create_stock_validation(): void
    {
        $product = Product::factory()->create();

        $response = $this->postJson(route('products.stock.create'), [
            'product_id' => $product->id,
            // Missing required fields
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku', 'quantity', 'price', 'date']);
    }

    public function test_create_stock_unique_sku_validation(): void
    {
        $product = Product::factory()->create();
        ProductStock::factory()->create(['sku' => 'EXISTING-SKU']);

        $response = $this->postJson(route('products.stock.create'), [
            'product_id' => $product->id,
            'sku' => 'EXISTING-SKU',
            'quantity' => 10,
            'price' => 100,
            'date' => now()->toDateString(),
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['sku']);
    }
}
