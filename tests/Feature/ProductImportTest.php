<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class ProductImportTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create();
        $this->actingAs($this->admin);
    }

    public function test_bulk_import_creates_product_with_stock_and_attribute_values(): void
    {
        $brand = Brand::create([
            'name' => 'Test Brand',
            'slug' => 'test-brand',
            'status' => true,
        ]);

        $category = Category::create([
            'name' => 'Test Category',
            'slug' => 'test-category',
            'status' => true,
        ]);

        $csv = implode("\n", [
            'name,slug,sku,base_price,stock_sku,stock_quantity,stock_price,attribute_name,attribute_value,brand_id,category_ids',
            "Test Product,test-product,TP-001,120.00,TP-001-S,5,130.00,Size,Small,{$brand->id},{$category->id}",
            "Test Product,test-product,TP-001,120.00,TP-001-M,3,140.00,Size,Medium,{$brand->id},{$category->id}",
        ]);

        $file = UploadedFile::fake()->createWithContent('products.csv', $csv);

        $response = $this->post(route('products.import.store'), [
            'file' => $file,
        ]);

        $response->assertRedirect(route('products.index'));

        $product = Product::where('slug', 'test-product')->first();
        $this->assertNotNull($product);
        $this->assertDatabaseHas('products', [
            'id' => $product->id,
            'slug' => 'test-product',
            'sku' => 'TP-001',
            'base_price' => 120.00,
        ]);

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'sku' => 'TP-001-S',
            'quantity' => 5,
            'price' => 130.00,
        ]);

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'sku' => 'TP-001-M',
            'quantity' => 3,
            'price' => 140.00,
        ]);

        $this->assertDatabaseHas('attributes', [
            'name' => 'Size',
        ]);

        $this->assertDatabaseHas('attribute_values', [
            'value' => 'Small',
        ]);
    }
}
