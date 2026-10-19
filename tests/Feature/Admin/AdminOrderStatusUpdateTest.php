<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderStatusUpdateTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::query()->create([
            'role' => 'admin',
            'name' => 'Admin',
            'username' => 'admin-'.Str::random(8),
            'email' => 'admin-'.Str::random(8).'@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function order(string $status): Order
    {
        return Order::query()->create([
            'order_number' => 'ORD-'.strtoupper(Str::random(8)),
            'status' => $status,
            'customer_name' => 'Customer',
            'customer_email' => 'cust@example.com',
            'subtotal_amount' => 100000,
            'total_amount' => 100000,
            'currency' => 'IDR',
            'expired_at' => now()->addHours(24),
        ]);
    }

    public function test_admin_can_complete_a_paid_order(): void
    {
        $admin = $this->admin();
        $order = $this->order('paid');

        $this->actingAs($admin)
            ->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'completed'])
            ->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->assertSame('completed', $order->fresh()->status);
    }

    public function test_admin_cannot_complete_a_pending_order(): void
    {
        $admin = $this->admin();
        $order = $this->order('pending');

        $this->actingAs($admin)
            ->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'completed'])
            ->assertStatus(422);
    }

    public function test_admin_can_cancel_pending_order(): void
    {
        $admin = $this->admin();
        $order = $this->order('pending');

        $this->actingAs($admin)
            ->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'cancelled'])
            ->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertSame('cancelled', $order->fresh()->status);
    }

    public function test_invalid_status_value_is_rejected(): void
    {
        $admin = $this->admin();
        $order = $this->order('paid');

        $this->actingAs($admin)
            ->patchJson("/api/admin/orders/{$order->id}/status", ['status' => 'shipped'])
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }
}
