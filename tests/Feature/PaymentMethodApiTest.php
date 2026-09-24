<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\User;
use Database\Seeders\PaymentMethodSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentMethodApiTest extends TestCase
{
    use RefreshDatabase;

    private function user(): User
    {
        return User::query()->create([
            'role' => 'user',
            'name' => 'Test User',
            'username' => 'user-'.Str::random(8),
            'email' => 'user-'.Str::random(8).'@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    private function admin(): User
    {
        return User::query()->create([
            'role' => 'admin',
            'name' => 'Admin User',
            'username' => 'admin-'.Str::random(8),
            'email' => 'admin-'.Str::random(8).'@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    public function test_public_can_list_only_active_payment_methods(): void
    {
        PaymentMethod::query()->create([
            'name' => 'Active Method',
            'code' => 'active_method',
            'gateway' => 'manual',
            'is_active' => true,
        ]);

        PaymentMethod::query()->create([
            'name' => 'Inactive Method',
            'code' => 'inactive_method',
            'gateway' => 'manual',
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/payment-methods');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'active_method');
    }

    public function test_admin_can_list_all_payment_methods_with_filter(): void
    {
        $admin = $this->admin();

        PaymentMethod::query()->create([
            'name' => 'Method 1',
            'code' => 'method_1',
            'gateway' => 'manual',
            'is_active' => true,
        ]);

        PaymentMethod::query()->create([
            'name' => 'Method 2',
            'code' => 'method_2',
            'gateway' => 'midtrans',
            'is_active' => false,
        ]);

        $response = $this->actingAs($admin)->getJson('/api/admin/payment-methods');
        $response->assertOk()->assertJsonCount(2, 'data');

        $filtered = $this->actingAs($admin)->getJson('/api/admin/payment-methods?gateway=midtrans');
        $filtered->assertOk()->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.code', 'method_2');

        $this->actingAs($admin)
            ->getJson('/api/admin/payment-methods?is_active=not-a-boolean')
            ->assertUnprocessable();
    }

    public function test_admin_can_create_payment_method(): void
    {
        $admin = $this->admin();

        $response = $this->actingAs($admin)->postJson('/api/admin/payment-methods', [
            'name' => 'Midtrans VA BCA',
            'code' => 'midtrans_va_bca',
            'gateway' => 'midtrans',
            'is_active' => true,
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.code', 'midtrans_va_bca');

        $this->assertDatabaseHas('payment_methods', [
            'code' => 'midtrans_va_bca',
        ]);
    }

    public function test_admin_cannot_create_unsupported_gateway(): void
    {
        $this->actingAs($this->admin())->postJson('/api/admin/payment-methods', [
            'name' => 'Unsupported Method',
            'code' => 'unsupported_method',
            'gateway' => 'unsupported',
            'is_active' => true,
        ])->assertUnprocessable();

        $this->assertDatabaseMissing('payment_methods', ['code' => 'unsupported_method']);
    }

    public function test_public_list_hides_active_unsupported_gateway(): void
    {
        PaymentMethod::query()->create([
            'name' => 'Unsupported Method',
            'code' => 'unsupported_method',
            'gateway' => 'unsupported',
            'is_active' => true,
        ]);

        $this->getJson('/api/payment-methods')
            ->assertOk()
            ->assertJsonCount(0, 'data');
    }

    public function test_payment_method_seeder_retires_legacy_gateways(): void
    {
        PaymentMethod::query()->create([
            'name' => 'Unsupported Method',
            'code' => 'unsupported_method',
            'gateway' => 'unsupported',
            'is_active' => true,
        ]);

        $this->seed(PaymentMethodSeeder::class);

        $this->assertDatabaseHas('payment_methods', [
            'code' => 'unsupported_method',
            'is_active' => false,
        ]);
        $this->assertDatabaseHas('payment_methods', [
            'code' => 'manual_bca',
            'is_active' => true,
        ]);
        $this->assertDatabaseHas('payment_methods', [
            'code' => 'midtrans_va_bca',
            'is_active' => true,
        ]);
    }

    public function test_admin_can_update_payment_method(): void
    {
        $admin = $this->admin();

        $method = PaymentMethod::query()->create([
            'name' => 'Old Name',
            'code' => 'old_code',
            'gateway' => 'manual',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->patchJson("/api/admin/payment-methods/{$method->id}", [
            'name' => 'New Name',
            'is_active' => false,
        ]);

        $response->assertOk()
            ->assertJsonPath('data.name', 'New Name')
            ->assertJsonPath('data.is_active', false);

        $this->assertDatabaseHas('payment_methods', [
            'id' => $method->id,
            'name' => 'New Name',
            'is_active' => false,
        ]);
    }

    public function test_admin_can_delete_unused_payment_method(): void
    {
        $admin = $this->admin();

        $method = PaymentMethod::query()->create([
            'name' => 'To Delete',
            'code' => 'to_delete',
            'gateway' => 'manual',
            'is_active' => true,
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/admin/payment-methods/{$method->id}");

        $response->assertOk();
        $this->assertDatabaseMissing('payment_methods', ['id' => $method->id]);
    }

    public function test_admin_cannot_delete_payment_method_in_use(): void
    {
        $admin = $this->admin();
        $user = $this->user();

        $method = PaymentMethod::query()->create([
            'name' => 'Used Method',
            'code' => 'used_method',
            'gateway' => 'manual',
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-TEST-01',
            'status' => 'pending',
            'customer_name' => 'Test',
            'customer_email' => 'test@example.com',
            'subtotal_amount' => 100000,
            'total_amount' => 100000,
            'currency' => 'IDR',
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'status' => 'pending',
            'amount' => 100000,
        ]);

        $response = $this->actingAs($admin)->deleteJson("/api/admin/payment-methods/{$method->id}");

        $response->assertStatus(409);
        $this->assertDatabaseHas('payment_methods', ['id' => $method->id]);
    }
}
