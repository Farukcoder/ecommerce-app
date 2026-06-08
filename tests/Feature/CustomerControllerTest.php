<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Order;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_view_customers_list(): void
    {
        $admin = User::factory()->create();

        $customerRole = Role::firstOrCreate([
            'slug' => 'customer',
        ], [
            'name' => 'Customer',
        ]);

        $customer = User::factory()->create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
        ]);
        $customer->assignRole($customerRole);

        $response = $this->actingAs($admin)->get(route('customers.index'));

        $response->assertOk()
            ->assertSee('Jane Customer')
            ->assertSee('jane@example.com');
    }

    public function test_authenticated_user_can_view_customer_details(): void
    {
        $admin = User::factory()->create();

        $customerRole = Role::firstOrCreate([
            'slug' => 'customer',
        ], [
            'name' => 'Customer',
        ]);

        $customer = User::factory()->create([
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
        ]);
        $customer->assignRole($customerRole);

        // Create an order for the customer
        Order::create([
            'customer_id' => $customer->id,
            'subtotal' => 1400.00,
            'discount_amount' => 0.00,
            'shipping_charge' => 100.00,
            'tax_amount' => 0.00,
            'total_amount' => 1500.00,
            'payment_status' => 'paid',
            'payment_method' => 'cod',
            'status' => 'delivered',
            'shipping_address' => [
                'name' => 'Jane Customer',
                'phone' => '01700000000',
                'address' => '123 Test Street',
                'area' => 'Mirpur',
                'city' => 'Dhaka',
                'zip' => '1216'
            ]
        ]);

        $response = $this->actingAs($admin)->get(route('customers.show', $customer));

        $response->assertOk()
            ->assertSee('Jane Customer')
            ->assertSee('jane@example.com')
            ->assertSee('৳1,500.00')
            ->assertSee('123 Test Street')
            ->assertSee('Mirpur');
    }

    public function test_authenticated_user_gets_404_for_non_customer_details(): void
    {
        $admin = User::factory()->create();
        $nonCustomer = User::factory()->create(); // Doesn't have 'customer' role

        $response = $this->actingAs($admin)->get(route('customers.show', $nonCustomer));

        $response->assertStatus(404);
    }
}
