<?php

namespace Tests\Feature;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerCheckoutTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_preview_and_place_order(): void
    {
        $customerRole = Role::firstOrCreate([
            'slug' => 'customer',
        ], [
            'name' => 'Customer',
        ]);

        $customer = User::factory()->create([
            'email' => 'buyer@example.com',
            'password' => Hash::make('Password123!'),
        ]);
        $customer->assignRole($customerRole);

        $brand = Brand::create([
            'name' => 'Nova',
            'slug' => 'nova',
            'status' => true,
        ]);

        $product = Product::create([
            'brand_id' => $brand->id,
            'name' => 'Steering Wheel Cover',
            'slug' => 'steering-wheel-cover',
            'sku' => 'SWC-001',
            'base_price' => 35889,
            'sale_price' => null,
            'featured' => true,
            'status' => 'Published',
        ]);

        ProductStock::create([
            'product_id' => $product->id,
            'sku' => 'SWC-001-A',
            'quantity' => 5,
        ]);

        $token = $customer->createToken('customer-api-token', ['customer'])->plainTextToken;

        $headers = ['Authorization' => 'Bearer ' . $token];

        $quote = $this->withHeaders($headers)->postJson('/api/customer/checkout/quote', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
        ]);

        $quote->assertOk()
            ->assertJsonPath('data.subtotal', 35889)
            ->assertJsonPath('data.tax_amount', 2871.12)
            ->assertJsonPath('data.total_amount', 38760.12);

        $order = $this->withHeaders($headers)->postJson('/api/customer/orders', [
            'items' => [
                [
                    'product_id' => $product->id,
                    'quantity' => 1,
                ],
            ],
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => '+8801XXXX-XXXXXX',
            'address' => '123 Main Street',
            'apartment' => 'Apt 4B',
            'city' => 'Dhaka',
            'division' => 'Dhaka',
            'zip' => '1205',
            'country' => 'Bangladesh',
            'payment_method' => 'cod',
            'note' => 'Please call before delivery.',
        ]);

        $order->assertCreated()
            ->assertJsonPath('data.payment_method', 'cod')
            ->assertJsonPath('data.total_amount', 38760.12)
            ->assertJsonPath('data.shipping_address.name', 'John Doe')
            ->assertJsonPath('data.items.0.product_name', 'Steering Wheel Cover');

        $this->assertDatabaseHas('orders', [
            'customer_id' => $customer->id,
            'payment_method' => 'cod',
            'total_amount' => 38760.12,
        ]);

        $this->assertDatabaseHas('order_items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->assertDatabaseHas('product_stocks', [
            'product_id' => $product->id,
            'quantity' => 4,
        ]);
    }
}
