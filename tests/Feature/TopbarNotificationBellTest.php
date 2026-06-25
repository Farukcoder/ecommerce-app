<?php

namespace Tests\Feature;

use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopbarNotificationBellTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_role_user_sees_notification_bell(): void
    {
        $customerRole = Role::firstOrCreate([
            'slug' => 'customer',
        ], [
            'name' => 'Customer',
        ]);

        $customer = User::factory()->create();
        $customer->assignRole($customerRole);

        $response = $this->actingAs($customer)->get(route('products.index'));

        $response->assertOk()
            ->assertSee('id="notifBellBtn"', false);
    }

    public function test_non_customer_role_user_does_not_see_notification_bell(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('products.index'));

        $response->assertOk()
            ->assertDontSee('id="notifBellBtn"', false);
    }
}
