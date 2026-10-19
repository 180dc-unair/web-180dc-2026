<?php

namespace Tests\Feature\Admin;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPaymentListTest extends TestCase
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

    private function order(string $orderNumber): Order
    {
        return Order::query()->create([
            'order_number' => $orderNumber,
            'status' => 'pending',
            'customer_name' => 'Customer',
            'customer_email' => 'cust@example.com',
            'subtotal_amount' => 100000,
            'total_amount' => 100000,
            'currency' => 'IDR',
            'expired_at' => now()->addHours(24),
        ]);
    }

    private function method(string $code, string $gateway): PaymentMethod
    {
        return PaymentMethod::query()->create([
            'name' => $code,
            'code' => $code,
            'gateway' => $gateway,
            'is_active' => true,
        ]);
    }

    private function payment(string $status, string $gateway, string $orderNumber): Payment
    {
        $order = $this->order($orderNumber);
        $method = $this->method("method_{$gateway}_{$orderNumber}", $gateway);

        return Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'gateway' => $gateway,
            'status' => $status,
            'amount' => 100000,
            'gateway_reference' => "ref-{$orderNumber}",
        ]);
    }

    public function test_admin_payments_index_filters_by_gateway_and_status(): void
    {
        $admin = $this->admin();
        $this->payment('pending', 'manual', 'ORD-P-1');
        $this->payment('paid', 'manual', 'ORD-P-2');
        $this->payment('pending', 'midtrans', 'ORD-P-3');

        $response = $this->actingAs($admin)->getJson('/api/admin/payments?gateway=manual&status=pending');

        $response->assertOk()
            ->assertJsonPath('meta.total', 1)
            ->assertJsonPath('data.0.order_id', fn ($id) => is_string($id));
    }

    public function test_admin_payments_show_returns_single_payment(): void
    {
        $admin = $this->admin();
        $payment = $this->payment('pending', 'manual', 'ORD-P-100');

        $this->actingAs($admin)
            ->getJson("/api/admin/payments/{$payment->id}")
            ->assertOk()
            ->assertJsonPath('data.id', $payment->id)
            ->assertJsonPath('data.gateway', 'manual');
    }

    public function test_non_admin_cannot_list_payments(): void
    {
        $user = User::query()->create([
            'role' => 'user',
            'name' => 'User',
            'username' => 'user-'.Str::random(8),
            'email' => 'user-'.Str::random(8).'@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->actingAs($user)
            ->getJson('/api/admin/payments')
            ->assertStatus(403);
    }
}
