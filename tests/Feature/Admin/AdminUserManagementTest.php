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

        $this->actingAs($admin)->postJson('/api/admin/users', [
            'name' => 'New User',
            'username' => 'new-user',
            'email' => 'new-user@example.com',
            'password' => 'secure-password',
        ])
            ->assertCreated()
            ->assertJsonPath('data.email', 'new-user@example.com')
            ->assertJsonPath('data.role', 'user')
            ->assertJsonMissingPath('data.password');

        $user = User::query()->where('email', 'new-user@example.com')->firstOrFail();
        $this->assertTrue(Hash::check('secure-password', $user->password));
        $this->assertSame('user', $user->role);

        $this->patchJson("/api/admin/users/{$user->id}", [
            'name' => 'Updated User',
            'username' => $user->username,
            'email' => $user->email,
            'password' => null,
        ])->assertOk()->assertJsonPath('data.role', 'user');

        $this->assertTrue(Hash::check('secure-password', $user->fresh()->password));
    }

    public function test_creating_user_with_role_admin_payload_is_rejected(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'name' => 'Account Admin',
            'username' => 'account-admin',
            'email' => 'account-admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)->postJson('/api/admin/users', [
            'name' => 'Privileged User',
            'username' => 'privileged-user',
            'email' => 'privileged@example.com',
            'role' => 'admin',
            'password' => 'secure-password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('role');

        $this->assertDatabaseMissing('users', ['email' => 'privileged@example.com']);
    }

    public function test_updating_user_with_role_admin_payload_is_rejected(): void
    {
        $admin = User::query()->create([
            'role' => 'admin',
            'name' => 'Account Admin',
            'username' => 'account-admin',
            'email' => 'account-admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $target = User::query()->create([
            'role' => 'user',
            'name' => 'Target User',
            'username' => 'target-user',
            'email' => 'target@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($admin)->patchJson("/api/admin/users/{$target->id}", [
            'role' => 'admin',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('role');

        $this->assertSame('user', $target->fresh()->role);
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
