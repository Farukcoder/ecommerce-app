<?php

namespace Database\Seeders;

use App\Models\User;
use HasinHayder\Tyro\Models\Privilege;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Arr;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'Super Admin', 'slug' => 'super-admin'],
            ['name' => 'Administrator', 'slug' => 'admin'],
            ['name' => 'User', 'slug' => 'user'],
            ['name' => 'Customer', 'slug' => 'customer'],
            ['name' => 'Editor', 'slug' => 'editor'],
            ['name' => 'All', 'slug' => '*'],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }

        $definitions = [
            [
                'name' => 'Generate Reports',
                'slug' => 'report.generate',
                'description' => 'Allows generating system-wide reports.',
                'roles' => ['admin', 'super-admin'],
            ],
            [
                'name' => 'Manage Users',
                'slug' => 'users.manage',
                'description' => 'Allows creating, editing, and deleting users.',
                'roles' => ['admin', 'super-admin'],
            ],
            [
                'name' => 'Manage Roles',
                'slug' => 'roles.manage',
                'description' => 'Allows editing roles, privileges, and role assignments.',
                'roles' => ['super-admin'],
            ],
            [
                'name' => 'View Billing',
                'slug' => 'billing.view',
                'description' => 'Allows viewing billing and payment information.',
                'roles' => ['admin', 'user'],
            ],
            [
                'name' => 'Manage Orders',
                'slug' => 'orders.manage',
                'description' => 'Allows managing customer orders and status updates.',
                'roles' => ['admin', 'super-admin'],
            ],
            [
                'name' => 'Wildcard',
                'slug' => '*',
                'description' => 'Grants every privilege.',
                'roles' => ['*'],
            ],
        ];

        $roleMap = Role::query()
            ->whereIn('slug', collect($definitions)->flatMap(fn (array $definition) => $definition['roles'])->unique()->all())
            ->get()
            ->keyBy('slug');

        foreach ($definitions as $definition) {
            $privilege = Privilege::updateOrCreate(
                ['slug' => $definition['slug']],
                Arr::only($definition, ['name', 'description'])
            );

            $roleIds = collect($definition['roles'])
                ->map(fn (string $slug) => $roleMap->get($slug)?->id)
                ->filter()
                ->unique()
                ->values()
                ->all();

            if (! empty($roleIds)) {
                $privilege->roles()->sync($roleIds);
            }
        }

        $users = [
            [
                'name' => 'System Administrator',
                'email' => env('SEED_ADMIN_EMAIL', 'admin@example.com'),
                'password' => env('SEED_ADMIN_PASSWORD', 'password'),
                'roles' => ['super-admin', 'admin'],
            ],
            [
                'name' => 'Demo User',
                'email' => env('SEED_USER_EMAIL', 'user@example.com'),
                'password' => env('SEED_USER_PASSWORD', 'password'),
                'roles' => ['user'],
            ],
            [
                'name' => 'Demo Customer',
                'email' => env('SEED_CUSTOMER_EMAIL', 'customer@example.com'),
                'password' => env('SEED_CUSTOMER_PASSWORD', 'password'),
                'roles' => ['customer'],
            ],
        ];

        foreach ($users as $userData) {
            $this->createOrUpdateUser($userData);
        }
    }

    protected function createOrUpdateUser(array $userData): void
    {
        $user = User::firstOrNew(['email' => $userData['email']]);
        $user->name = $userData['name'];

        if (! $user->exists) {
            $user->password = $userData['password'];
        }

        $user->save();

        foreach ($userData['roles'] as $slug) {
            $role = Role::where('slug', $slug)->first();

            if ($role && ! $user->roles->contains($role->id)) {
                $user->assignRole($role);
            }
        }
    }
}
