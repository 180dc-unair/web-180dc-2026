<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $adminRole = Role::query()->where('slug', 'admin')->first();

        if (! $adminRole) {
            $this->command->error('Role admin not found. Run RoleSeeder first.');

            return;
        }

        User::query()->firstOrCreate(
            ['email' => 'admin@180dc.com'],
            [
                'role_id' => $adminRole->id,
                'name' => 'Admin 180DC',
                'username' => 'admin',
                'email' => 'admin@180dc.com',
                'password' => 'password123',
            ],
        );
    }
}