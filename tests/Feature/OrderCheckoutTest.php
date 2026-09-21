<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $name = 'User'): User
    {
        return User::query()->create([
            'role' => 'user',
            'name' => $name,
            'username' => 'user-'.Str::random(8),
            'email' => 'user-'.Str::random(8).'@example.com',
            'password' => Hash::make('password'),
        ]);
    }

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

    private function createProduct(array $attributes = []): Product
    {
        return Product::query()->create(array_merge([
            'title' => 'Kaos 180DC',
            'slug' => 'kaos-'.Str::uuid(),
            'type' => 'physical',
            'status' => 'active',
            'price' => 150000,
            'stock' => 10,
        ], $attributes));
    }

    public function test_checkout_from_cart_clears_cart_and_snapshots_price(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['price' => 150000, 'stock' => 10]);

        // Tambah item ke cart
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertCreated();

        // Checkout
        $response = $this->actingAs($user)->postJson('/api/orders', [
            'customer_name' => 'Test User',
            'customer_email' => 'test@example.com',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.total_amount', '300000.00')
            ->assertJsonPath('data.currency', 'IDR');

        // Order items snapshot dibuat
        $this->assertDatabaseCount('order_items', 1);
        $this->assertDatabaseHas('order_items', [
            'name' => 'Kaos 180DC',
            'quantity' => 2,
            'unit_price' => 150000,
        ]);

        // Cart dikosongkan
        $this->assertDatabaseCount('cart_items', 0);

        // Ubah harga produk setelah checkout
        $product->update(['price' => 200000]);

        // Order items harga TIDAK berubah (sudah di-snapshot)
        $this->assertDatabaseHas('order_items', [
            'unit_price' => 150000,
        ]);
    }

    public function test_checkout_empty_cart_422(): void
    {
        $user = $this->user();

        $response = $this->actingAs($user)->postJson('/api/orders', [
            'customer_name' => 'Test',
        ]);

        $response->assertUnprocessable();
    }

    public function test_checkout_product_inactive_409(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['status' => 'active', 'stock' => 10]);

        // Tambah ke cart saat produk masih aktif
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        // Nonaktifkan produk setelah ditambah ke cart
        $product->update(['status' => 'inactive']);

        $response = $this->actingAs($user)->postJson('/api/orders');

        $response->assertStatus(409)
            ->assertJsonPath('status', 'error');

        // Order tidak terbuat
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_insufficient_stock_409(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['stock' => 5]);

        // Tambah 3 ke cart
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ])->assertCreated();

        // Kurangi stok setelah ditambah ke cart (simulasi user lain beli)
        $product->update(['stock' => 2]);

        $response = $this->actingAs($user)->postJson('/api/orders');

        $response->assertStatus(409)
            ->assertJsonPath('status', 'error');

        // Order tidak terbuat
        $this->assertDatabaseCount('orders', 0);
    }

    public function test_checkout_idempotency_same_key_returns_same_order(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['price' => 50000, 'stock' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $idempotencyKey = Str::uuid()->toString();

        // Checkout pertama
        $response1 = $this->actingAs($user)->postJson('/api/orders', [
            'customer_name' => 'Test',
        ], ['Idempotency-Key' => $idempotencyKey]);

        $response1->assertCreated();
        $orderId = $response1->json('data.id');

        // Checkout kedua dengan key sama → harus return order yang sama
        $response2 = $this->actingAs($user)->postJson('/api/orders', [
            'customer_name' => 'Test',
        ], ['Idempotency-Key' => $idempotencyKey]);

        $response2->assertCreated()
            ->assertJsonPath('data.id', $orderId);

        // Hanya 1 order yang terbuat (bukan 2)
        $this->assertDatabaseCount('orders', 1);
    }

    public function test_checkout_rejects_invalid_idempotency_key_header(): void
    {
        $this->actingAs($this->user())
            ->postJson('/api/orders', [], ['Idempotency-Key' => 'not-a-uuid'])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('idempotency_key');
    }

    public function test_user_cannot_see_other_user_order(): void
    {
        $userA = $this->user('User A');
        $userB = $this->user('User B');
        $product = $this->createProduct(['stock' => 10]);

        // User A buat order
        $this->actingAs($userA)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $orderResponse = $this->actingAs($userA)->postJson('/api/orders')
            ->assertCreated();

        $orderId = $orderResponse->json('data.id');

        // User B coba lihat → 404 (bukan 403, anti-enumeration)
        $this->actingAs($userB)->getJson("/api/orders/{$orderId}")
            ->assertNotFound();
    }

    public function test_cancel_pending_succeeds(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['stock' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $orderResponse = $this->actingAs($user)->postJson('/api/orders')
            ->assertCreated();

        $orderId = $orderResponse->json('data.id');

        // Cancel
        $response = $this->actingAs($user)->postJson("/api/orders/{$orderId}/cancel");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'cancelled',
        ]);
    }

    public function test_cancel_paid_409(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['stock' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $orderResponse = $this->actingAs($user)->postJson('/api/orders')
            ->assertCreated();

        $orderId = $orderResponse->json('data.id');

        // Simulasi paid (biasanya lewat webhook, di sini langsung update DB)
        Order::where('id', $orderId)->update(['status' => 'paid', 'paid_at' => now()]);

        // Cancel → 409
        $this->actingAs($user)->postJson("/api/orders/{$orderId}/cancel")
            ->assertStatus(409);
    }

    public function test_order_with_pending_midtrans_payment_cannot_be_cancelled_locally(): void
    {
        $user = $this->user();
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-'.strtoupper(Str::random(10)),
            'status' => 'pending',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'subtotal_amount' => 100000,
            'total_amount' => 100000,
            'currency' => 'IDR',
            'expired_at' => now()->addHour(),
        ]);
        $method = PaymentMethod::query()->create([
            'name' => 'Midtrans VA BCA',
            'code' => 'midtrans_va_bca',
            'gateway' => 'midtrans',
            'is_active' => true,
        ]);
        Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'gateway' => 'midtrans',
            'status' => 'pending',
            'amount' => 100000,
            'gateway_reference' => (string) Str::uuid(),
            'expired_at' => now()->addHour(),
        ]);

        $this->actingAs($user)
            ->postJson("/api/orders/{$order->id}/cancel")
            ->assertStatus(409);

        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_order_number_unique(): void
    {
        $user = $this->user();
        $productA = $this->createProduct(['stock' => 10]);
        $productB = $this->createProduct(['stock' => 10]);

        // Order 1
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $productA->id,
            'quantity' => 1,
        ])->assertCreated();
        $r1 = $this->actingAs($user)->postJson('/api/orders')->assertCreated();

        // Order 2
        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $productB->id,
            'quantity' => 1,
        ])->assertCreated();
        $r2 = $this->actingAs($user)->postJson('/api/orders')->assertCreated();

        // Order number harus berbeda
        $this->assertNotEquals(
            $r1->json('data.order_number'),
            $r2->json('data.order_number')
        );
    }

    public function test_checkout_retries_order_number_collision(): void
    {
        $user = $this->user();
        $product = $this->createProduct();
        $collision = 'ORD-20260921-COLLIDE';
        $unique = 'ORD-20260921-UNIQUE';

        Order::query()->create([
            'user_id' => $user->id,
            'order_number' => $collision,
            'status' => 'pending',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'subtotal_amount' => 100000,
            'total_amount' => 100000,
            'currency' => 'IDR',
            'expired_at' => now()->addHour(),
        ]);

        $this->mock(OrderNumberService::class)
            ->shouldReceive('generate')
            ->twice()
            ->andReturn($collision, $unique);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $this->actingAs($user)->postJson('/api/orders')
            ->assertCreated()
            ->assertJsonPath('data.order_number', $unique);
    }

    public function test_expired_via_scheduler(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['stock' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $orderResponse = $this->actingAs($user)->postJson('/api/orders')
            ->assertCreated();

        $orderId = $orderResponse->json('data.id');

        // Simulasi expired (set expired_at ke masa lalu)
        Order::where('id', $orderId)->update(['expired_at' => now()->subHour()]);

        // Jalankan scheduler command
        $this->artisan('orders:expire')
            ->assertSuccessful();

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
            'status' => 'expired',
        ]);
    }

    public function test_stock_not_decremented_on_checkout(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['stock' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 3,
        ])->assertCreated();

        $this->actingAs($user)->postJson('/api/orders')
            ->assertCreated();

        // Stok TIDAK berkurang saat checkout (berkurang saat payment paid di Fase 3)
        $product->refresh();
        $this->assertEquals(10, $product->stock, 'Stock should NOT decrement on checkout');
    }

    public function test_list_orders_paginated(): void
    {
        $user = $this->user();

        // Buat 3 order
        for ($i = 0; $i < 3; $i++) {
            $product = $this->createProduct(['stock' => 10]);
            $this->actingAs($user)->postJson('/api/cart/items', [
                'product_id' => $product->id,
                'quantity' => 1,
            ])->assertCreated();
            $this->actingAs($user)->postJson('/api/orders')->assertCreated();
        }

        $response = $this->actingAs($user)->getJson('/api/orders?per_page=2');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.total', 3)
            ->assertJsonPath('meta.per_page', 2);
    }

    public function test_guest_cannot_access_orders(): void
    {
        $this->postJson('/api/orders')->assertUnauthorized();
        $this->getJson('/api/orders')->assertUnauthorized();
    }

    public function test_checkout_direct_items_without_cart(): void
    {
        $user = $this->user();
        $product = $this->createProduct(['price' => 75000, 'stock' => 10]);

        // Direct checkout tanpa cart (Mode B)
        $response = $this->actingAs($user)->postJson('/api/orders', [
            'customer_name' => 'Direct Buyer',
            'customer_email' => 'direct@example.com',
            'items' => [
                [
                    'item_type' => 'product',
                    'product_id' => $product->id,
                    'quantity' => 2,
                ],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.total_amount', '150000.00');

        $this->assertDatabaseHas('order_items', [
            'name' => 'Kaos 180DC',
            'quantity' => 2,
            'unit_price' => 75000,
        ]);
    }

    public function test_admin_can_view_any_order(): void
    {
        $user = $this->user('Regular User');
        $admin = $this->admin();
        $product = $this->createProduct(['stock' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $orderId = $this->actingAs($user)->postJson('/api/orders')
            ->assertCreated()
            ->json('data.id');

        // Admin bisa melihat order milik user lain
        $this->actingAs($admin)->getJson("/api/orders/{$orderId}")
            ->assertOk()
            ->assertJsonPath('data.id', $orderId);
    }

    public function test_admin_update_status_flow(): void
    {
        $user = $this->user();
        $admin = $this->admin();
        $product = $this->createProduct(['stock' => 10]);

        $this->actingAs($user)->postJson('/api/cart/items', [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertCreated();

        $orderId = $this->actingAs($user)->postJson('/api/orders')
            ->assertCreated()
            ->json('data.id');

        // Admin tidak bisa selesaikan order yang masih pending (harus paid dulu)
        $this->actingAs($admin)->patchJson("/api/admin/orders/{$orderId}/status", [
            'status' => 'completed',
        ])->assertStatus(409);

        // Simulasi status paid (biasanya via webhook di Fase 3)
        Order::where('id', $orderId)->update(['status' => 'paid', 'paid_at' => now()]);

        $this->actingAs($admin)->patchJson("/api/admin/orders/{$orderId}/status", [
            'status' => 'cancelled',
        ])->assertStatus(409);

        // Sekarang admin bisa ubah status menjadi completed
        $this->actingAs($admin)->patchJson("/api/admin/orders/{$orderId}/status", [
            'status' => 'completed',
        ])->assertOk()
            ->assertJsonPath('data.status', 'completed');

        $this->actingAs($admin)->patchJson("/api/admin/orders/{$orderId}/status", [
            'status' => 'cancelled',
        ])->assertStatus(409);
    }
}
