<?php

namespace Tests\Feature;

use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_account_cannot_sign_in_through_admin_panel_login(): void
    {
        config(['tyro-login.captcha.enabled_login' => false]);

        $customerRole = Role::firstOrCreate([
            'slug' => 'customer',
        ], [
            'name' => 'Customer',
        ]);

        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => Hash::make('Password123!'),
        ]);

        $user->assignRole($customerRole);

        $response = $this->post('/login', [
            'email' => 'customer@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');

        $this->assertGuest();
    }
}
