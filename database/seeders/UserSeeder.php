<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $password = Hash::make('password');

        User::query()->firstOrCreate(
            ['email' => 'admin@180dc.com'],
            [
                'role' => 'admin',
                'name' => 'Admin',
                'username' => 'admin',
                'email' => 'admin@180dc.com',
                'password' => $password,
            ],
        );

        User::query()->firstOrCreate(
            ['email' => 'admin2@180dc.com'],
            [
                'role' => 'admin',
                'name' => 'Admin 2',
                'username' => 'admin2',
                'email' => 'admin2@180dc.com',
                'password' => $password,
            ],
        );

        User::query()->firstOrCreate(
            ['email' => 'admin3@180dc.com'],
            [
                'role' => 'admin',
                'name' => 'Admin 3',
                'username' => 'admin3',
                'email' => 'admin3@180dc.com',
                'password' => $password,
            ],
        );

        User::query()->firstOrCreate(
            ['email' => 'user@180dc.com'],
            [
                'role' => 'user',
                'name' => 'User',
                'username' => 'user',
                'email' => 'user@180dc.com',
                'password' => $password,
            ],
        );

        User::query()->firstOrCreate(
            ['email' => 'user2@180dc.com'],
            [
                'role' => 'user',
                'name' => 'User 2',
                'username' => 'user2',
                'email' => 'user2@180dc.com',
                'password' => $password,
            ],
        );
    }
}
