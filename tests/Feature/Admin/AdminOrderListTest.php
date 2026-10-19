<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminOrderListTest extends TestCase
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

    private function createOrder(string $status, string $orderNumber, array $overrides = []): Order
    {
        return Order::query()->create(array_merge([
            'order_number' => $orderNumber,
            'status' => $status,
            'customer_name' => 'Customer '.$orderNumber,
            'customer_email' => Str::lower($orderNumber).'@example.com',
            'subtotal_amount' => 100000,
            'total_amount' => 100000,
            'currency' => 'IDR',
            'expired_at' => now()->addHours(24),
        ], $overrides));
    }

    public function test_admin_orders_index_paginates_and_filters_by_status(): void
    {
        $admin = $this->admin();
        $this->createOrder('pending', 'ORD-A-001');
        $this->createOrder('paid', 'ORD-A-002');
        $this->createOrder('paid', 'ORD-A-003');
        $this->createOrder('cancelled', 'ORD-A-004');

        $response = $this->actingAs($admin)->getJson('/api/admin/orders?status=paid&per_page=10');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('meta.total', 2);

        $items = collect($response->json('data'));
        $this->assertSame(['ORD-A-002', 'ORD-A-003'], $items->pluck('order_number')->all());
    }

    public function test_admin_orders_index_search_matches_order_number_or_email(): void
    {
        $admin = $this->admin();
        $this->createOrder('pending', 'ORD-SEARCH-1', ['customer_email' => 'match@example.com']);
        $this->createOrder('pending', 'ORD-OTHER-9');

        $response = $this->actingAs($admin)->getJson('/api/admin/orders?search=match');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.order_number', 'ORD-SEARCH-1');
    }

    public function test_admin_orders_rejects_invalid_status(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)
            ->getJson('/api/admin/orders?status=bogus')
            ->assertStatus(422)
            ->assertJsonValidationErrors('status');
    }

    public function test_non_admin_cannot_list_orders(): void
    {
        $user = User::query()->create([
            'role' => 'user',
            'name' => 'User',
            'username' => 'user-'.Str::random(8),
            'email' => 'user-'.Str::random(8).'@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->getJson('/api/admin/orders')
            ->assertStatus(403);
    }
}
