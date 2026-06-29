<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DatabaseSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_database_seeder_populates_system_settings_roles_users_and_catalog(): void
    {
        $this->artisan('db:seed')->assertExitCode(0);

        $this->assertDatabaseHas('system_settings', ['system_name' => 'Active eCommerce CMS']);
        $this->assertDatabaseHas('roles', ['slug' => 'admin']);
        $this->assertDatabaseHas('privileges', ['slug' => 'users.manage']);
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com']);
        $this->assertDatabaseHas('categories', ['slug' => 'electronics']);
        $this->assertDatabaseHas('brands', ['slug' => 'apple']);
    }
}
