<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_and_update_a_user(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'name' => 'Account Admin',
            'username' => 'account-admin',
            'email' => 'account-admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $created = $this->actingAs($admin)->postJson('/api/admin/users', [
            'name' => 'New User',
            'username' => 'new-user',
            'email' => 'new-user@example.com',
            'role' => 'user',
            'password' => 'secure-password',
        ]);

        $created
            ->assertCreated()
            ->assertJsonPath('data.email', 'new-user@example.com')
            ->assertJsonMissingPath('data.password');

        $user = User::query()->where('email', 'new-user@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('secure-password', $user->password));

        $this->patchJson("/api/admin/users/{$user->id}", [
            'name' => 'Updated User',
            'username' => $user->username,
            'email' => $user->email,
            'role' => 'admin',
            'password' => null,
        ])->assertOk()->assertJsonPath('data.role', 'admin');

        $this->assertTrue(Hash::check('secure-password', $user->fresh()->password));
    }

    public function test_admin_cannot_delete_their_current_account(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'name' => 'Protected Admin',
            'username' => 'protected-admin',
            'email' => 'protected@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)
            ->deleteJson("/api/admin/users/{$admin->id}")
            ->assertUnprocessable();

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }
}
