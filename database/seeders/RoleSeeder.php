<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            ['name' => 'User', 'slug' => 'user'],
            ['name' => 'Admin', 'slug' => 'admin'],
        ];

        foreach ($roles as $role) {
            Role::query()->firstOrCreate(
                ['slug' => $role['slug']], $role
            );
        }
    }
}
