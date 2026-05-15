<?php

namespace Tests\Feature;

use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CustomerAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_receive_token(): void
    {
        $response = $this->postJson('/api/customer/register', [
            'name' => 'Jane Customer',
            'email' => 'jane@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertCreated()
            ->assertJsonPath('user.email', 'jane@example.com')
            ->assertJsonPath('user.roles.0', 'customer')
            ->assertJsonStructure([
                'message',
                'token_type',
                'access_token',
                'user' => ['id', 'name', 'email', 'roles'],
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'jane@example.com',
        ]);
    }

    public function test_customer_can_login_and_access_profile(): void
    {
        $customerRole = Role::firstOrCreate([
            'slug' => 'customer',
        ], [
            'name' => 'Customer',
        ]);

        $user = User::factory()->create([
            'email' => 'john@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $user->assignRole($customerRole);

        $login = $this->postJson('/api/customer/login', [
            'email' => 'john@example.com',
            'password' => 'Password123!',
        ]);

        $login->assertOk()
            ->assertJsonPath('user.email', 'john@example.com')
            ->assertJsonPath('user.roles.0', 'customer');

        $token = $login->json('access_token');

        $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/customer/me')
            ->assertOk()
            ->assertJsonPath('user.email', 'john@example.com');
    }

    public function test_non_customer_cannot_use_customer_login(): void
    {
        User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $response = $this->postJson('/api/customer/login', [
            'email' => 'admin@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertForbidden()
            ->assertJsonPath('message', 'This account is not allowed to use the customer API.');
    }
}
