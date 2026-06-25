<?php

namespace Tests\Feature\Api\Customer;

use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductStock;
use App\Models\User;
use App\Notifications\OrderPlaced;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Notifications\DatabaseNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    private function makeCustomerWithProduct(): array
    {
        $customerRole = Role::firstOrCreate(
            ['slug' => 'customer'],
            ['name' => 'Customer'],
        );

        $customer = User::factory()->create();
        $customer->assignRole($customerRole);

        $brand = Brand::create(['name' => 'TestBrand', 'slug' => 'testbrand', 'status' => true]);
        $product = Product::create([
            'brand_id' => $brand->id,
            'name' => 'Test Product',
            'slug' => 'test-product',
            'sku' => 'TP-001',
            'base_price' => 1000,
            'status' => 'Published',
        ]);
        ProductStock::create(['product_id' => $product->id, 'sku' => 'TP-001-A', 'quantity' => 10]);

        $token = $customer->createToken('customer-api-token', ['customer'])->plainTextToken;

        return compact('customer', 'product', 'token');
    }

    private function placeOrder(string $token, int $productId): TestResponse
    {
        return $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->postJson('/api/customer/orders', [
                'items' => [['product_id' => $productId, 'quantity' => 1]],
                'phone' => '+8801700000000',
                'address' => '123 Main Street',
                'city' => 'Dhaka',
                'payment_method' => 'cod',
            ]);
    }

    public function test_placing_order_creates_database_notification(): void
    {
        ['customer' => $customer, 'product' => $product, 'token' => $token] = $this->makeCustomerWithProduct();

        $this->placeOrder($token, $product->id)->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $customer->id,
            'notifiable_type' => User::class,
            'read_at' => null,
        ]);

        $notification = DatabaseNotification::where('notifiable_id', $customer->id)->first();
        $this->assertEquals('order_placed', $notification->data['type']);
        $this->assertStringContainsString('ORD-', $notification->data['order_number']);
    }

    public function test_customer_can_list_notifications(): void
    {
        ['customer' => $customer, 'product' => $product, 'token' => $token] = $this->makeCustomerWithProduct();
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->placeOrder($token, $product->id)->assertCreated();

        $response = $this->withHeaders($headers)->getJson('/api/customer/notifications');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [['id', 'type', 'data', 'read_at', 'created_at']],
                'unread_count',
                'pagination',
            ])
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('data.0.type', 'order_placed');
    }

    public function test_customer_can_mark_notification_as_read(): void
    {
        ['customer' => $customer, 'product' => $product, 'token' => $token] = $this->makeCustomerWithProduct();
        $headers = ['Authorization' => 'Bearer '.$token];

        $this->placeOrder($token, $product->id)->assertCreated();

        $notificationId = $customer->notifications()->first()->id;

        $response = $this->withHeaders($headers)
            ->patchJson("/api/customer/notifications/{$notificationId}/read");

        $response->assertOk()
            ->assertJsonPath('unread_count', 0);

        $this->assertDatabaseMissing('notifications', [
            'id' => $notificationId,
            'read_at' => null,
        ]);
    }

    public function test_customer_can_mark_all_notifications_as_read(): void
    {
        ['customer' => $customer, 'product' => $product, 'token' => $token] = $this->makeCustomerWithProduct();
        $headers = ['Authorization' => 'Bearer '.$token];

        // Place 2 orders to get 2 notifications
        $this->placeOrder($token, $product->id)->assertCreated();
        ProductStock::create(['product_id' => $product->id, 'sku' => 'TP-001-B', 'quantity' => 10]);
        $this->placeOrder($token, $product->id)->assertCreated();

        $this->assertSame(2, $customer->unreadNotifications()->count());

        $response = $this->withHeaders($headers)->patchJson('/api/customer/notifications/read-all');

        $response->assertOk()->assertJsonPath('unread_count', 0);

        $this->assertSame(0, $customer->unreadNotifications()->count());
    }

    public function test_mark_as_read_returns_404_for_other_customers_notification(): void
    {
        ['token' => $token] = $this->makeCustomerWithProduct();

        $secondCustomer = User::factory()->create();
        $customerRole = Role::firstOrCreate(['slug' => 'customer'], ['name' => 'Customer']);
        $secondCustomer->assignRole($customerRole);

        $notificationId = Str::uuid()->toString();
        DB::table('notifications')->insert([
            'id' => $notificationId,
            'type' => OrderPlaced::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $secondCustomer->id,
            'data' => json_encode(['type' => 'order_placed', 'order_number' => 'ORD-2026-00099', 'message' => 'test']),
            'read_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // First customer tries to mark second customer's notification as read
        $response = $this->withHeaders(['Authorization' => 'Bearer '.$token])
            ->patchJson("/api/customer/notifications/{$notificationId}/read");

        $response->assertNotFound();
    }

    public function test_unauthenticated_access_is_rejected(): void
    {
        $this->getJson('/api/customer/notifications')->assertUnauthorized();
    }
}
