<?php

namespace Tests\Feature;

use App\Exceptions\GatewayException;
use App\Models\MediaAsset;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.payments.manual', [
            'bank' => 'BCA',
            'account_number' => '1234567890',
            'account_name' => '180DC Uniar',
        ]);
    }

    private function user(): User
    {
        return User::query()->create([
            'role' => 'user',
            'name' => 'User One',
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

    private function createMethod(string $code = 'manual_bca', string $gateway = 'manual'): PaymentMethod
    {
        return PaymentMethod::query()->create([
            'name' => 'Manual Transfer BCA',
            'code' => $code,
            'gateway' => $gateway,
            'is_active' => true,
        ]);
    }

    private function createOrder(User $user, array $attributes = []): Order
    {
        return Order::query()->create(array_merge([
            'user_id' => $user->id,
            'order_number' => 'ORD-'.strtoupper(Str::random(10)),
            'status' => 'pending',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'subtotal_amount' => 200000,
            'total_amount' => 200000,
            'currency' => 'IDR',
            'expired_at' => now()->addHours(24),
        ], $attributes));
    }

    public function test_create_payment_from_pending_order(): void
    {
        $user = $this->user();
        $method = $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user, ['total_amount' => 150000]);

        $response = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'manual_bca',
        ]);

        $response->assertCreated()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.order_id', $order->id)
            ->assertJsonPath('data.status', 'pending')
            ->assertJsonPath('data.amount', '150000.00');

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'status' => 'pending',
            'amount' => 150000.00,
        ]);
    }

    public function test_create_second_pending_payment_returns_409(): void
    {
        $user = $this->user();
        $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user);

        // First payment
        $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'manual_bca',
        ])->assertCreated();

        // Second payment attempt while first is still pending
        $response = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'manual_bca',
        ]);

        $response->assertStatus(409)
            ->assertJsonPath('status', 'error');
    }

    public function test_create_payment_for_paid_order_returns_409(): void
    {
        $user = $this->user();
        $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user, ['status' => 'paid']);

        $response = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'manual_bca',
        ]);

        $response->assertStatus(409);
    }

    public function test_create_payment_for_expired_order_returns_409(): void
    {
        $user = $this->user();
        $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user, [
            'status' => 'pending',
            'expired_at' => now()->subHour(),
        ]);

        $response = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'manual_bca',
        ]);

        $response->assertStatus(409);
    }

    public function test_user_can_poll_payment(): void
    {
        $user = $this->user();
        $method = $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'status' => 'pending',
            'amount' => $order->total_amount,
            'gateway_reference' => 'MANUAL-001',
        ]);

        $response = $this->actingAs($user)->getJson("/api/payments/{$payment->id}");

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.id', $payment->id)
            ->assertJsonPath('data.status', 'pending');
    }

    public function test_user_cannot_poll_other_users_payment(): void
    {
        $user1 = $this->user();
        $user2 = $this->user();
        $method = $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user1);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'gateway' => 'manual',
            'status' => 'pending',
            'amount' => $order->total_amount,
        ]);

        $response = $this->actingAs($user2)->getJson("/api/payments/{$payment->id}");
        $response->assertStatus(404);
    }

    public function test_user_can_cancel_pending_payment(): void
    {
        $user = $this->user();
        $method = $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'gateway' => 'manual',
            'status' => 'pending',
            'amount' => $order->total_amount,
        ]);

        $response = $this->actingAs($user)->postJson("/api/payments/{$payment->id}/cancel");

        $response->assertOk()
            ->assertJsonPath('data.status', 'cancelled');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_midtrans_payment_cannot_be_cancelled_locally(): void
    {
        $user = $this->user();
        $method = $this->createMethod('midtrans_va_bca', 'midtrans');
        $order = $this->createOrder($user);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'gateway' => 'midtrans',
            'status' => 'pending',
            'amount' => $order->total_amount,
            'gateway_reference' => (string) Str::uuid(),
            'expired_at' => now()->addHour(),
        ]);

        $this->actingAs($user)
            ->postJson("/api/payments/{$payment->id}/cancel")
            ->assertStatus(409);

        $this->assertSame('pending', $payment->fresh()->status);
    }

    public function test_admin_can_confirm_manual_payment(): void
    {
        $admin = $this->admin();
        $user = $this->user();
        $method = $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user);

        $product = Product::query()->create([
            'title' => 'Product Stock Test',
            'slug' => 'product-stock-test',
            'type' => 'physical',
            'status' => 'active',
            'price' => 200000,
            'stock' => 5,
            'sold_count' => 0,
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'item_type' => 'product',
            'name' => $product->title,
            'quantity' => 2,
            'unit_price' => 200000,
            'total_price' => 400000,
        ]);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'gateway' => 'manual',
            'status' => 'pending',
            'amount' => $order->total_amount,
            'gateway_reference' => 'MANUAL-'.$order->order_number.'-TEST',
            'proof_media_id' => MediaAsset::query()->create([
                'file_id' => 'proof-file-123',
                'url' => 'https://example.com/proof.jpg',
            ])->id,
            'expired_at' => now()->addHour(),
        ]);

        $response = $this->actingAs($admin)->postJson("/api/admin/payments/{$payment->id}/confirm");

        $response->assertOk()
            ->assertJsonPath('data.status', 'paid');

        $this->assertDatabaseHas('payments', [
            'id' => $payment->id,
            'status' => 'paid',
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'paid',
        ]);

        $product->refresh();
        $this->assertEquals(3, $product->stock);
        $this->assertEquals(2, $product->sold_count);
        $this->assertDatabaseHas('payment_webhooks', [
            'payment_id' => $payment->id,
            'gateway' => 'manual',
            'is_processed' => true,
        ]);
    }

    public function test_owner_can_upload_manual_payment_proof_once(): void
    {
        config()->set('services.imagekit.private_key', 'test-private-key');
        config()->set('services.imagekit.upload_url', 'https://upload.imagekit.io/api/v1/files/upload');
        Http::fake([
            'https://upload.imagekit.io/*' => Http::response([
                'fileId' => 'payment-proof-123',
                'url' => 'https://ik.imagekit.io/example/payment-proof.jpg',
            ]),
        ]);

        $user = $this->user();
        $otherUser = $this->user();
        $this->createMethod();
        $order = $this->createOrder($user);
        $paymentId = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'manual_bca',
        ])->assertCreated()->json('data.id');

        $this->actingAs($otherUser)->post("/api/payments/{$paymentId}/proof", [
            'proof' => UploadedFile::fake()->image('not-mine.png'),
        ], ['Accept' => 'application/json'])->assertNotFound();

        $response = $this->actingAs($user)->post("/api/payments/{$paymentId}/proof", [
            'proof' => UploadedFile::fake()->createWithContent(
                'proof.png',
                base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAusB9Wl2nGQAAAAASUVORK5CYII='),
            ),
        ], ['Accept' => 'application/json']);

        $response->assertCreated()
            ->assertJsonPath('data.proof.file_id', 'payment-proof-123')
            ->assertJsonPath('data.proof.url', 'https://ik.imagekit.io/example/payment-proof.jpg');
        $this->assertNotNull(Payment::query()->findOrFail($paymentId)->proof_media_id);
        $this->actingAs($user)->getJson("/api/orders/{$order->id}")
            ->assertOk()
            ->assertJsonPath('data.payments.0.id', $paymentId)
            ->assertJsonPath('data.payments.0.proof.file_id', 'payment-proof-123');

        $this->actingAs($user)->post("/api/payments/{$paymentId}/proof", [
            'proof' => UploadedFile::fake()->image('second.png'),
        ], ['Accept' => 'application/json'])->assertStatus(409);
        Http::assertSentCount(1);
    }

    public function test_admin_cannot_confirm_manual_payment_without_proof(): void
    {
        $admin = $this->admin();
        $user = $this->user();
        $method = $this->createMethod();
        $order = $this->createOrder($user);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'gateway' => 'manual',
            'status' => 'pending',
            'amount' => $order->total_amount,
            'gateway_reference' => 'MANUAL-'.$order->order_number.'-NO-PROOF',
            'expired_at' => now()->addHour(),
        ]);

        $this->actingAs($admin)
            ->postJson("/api/admin/payments/{$payment->id}/confirm")
            ->assertStatus(409);
        $this->assertDatabaseMissing('payment_webhooks', ['payment_id' => $payment->id]);
    }

    public function test_scheduler_expires_pending_payments(): void
    {
        $user = $this->user();
        $method = $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user);

        $expiredPayment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'status' => 'pending',
            'amount' => 100000,
            'expired_at' => now()->subMinutes(10),
        ]);

        $activePayment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'status' => 'pending',
            'amount' => 100000,
            'expired_at' => now()->addHours(2),
        ]);

        $this->artisan('payments:expire-pending')->assertSuccessful();

        $expiredPayment->refresh();
        $activePayment->refresh();

        $this->assertEquals('expired', $expiredPayment->status);
        $this->assertEquals('pending', $activePayment->status);
    }

    public function test_payment_creation_returns_only_manual_instructions(): void
    {
        $user = $this->user();
        $this->createMethod('manual_bca', 'manual');
        $order = $this->createOrder($user);

        $response = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'manual_bca',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.instructions.bank', 'BCA')
            ->assertJsonPath('data.instructions.account_number', '1234567890')
            ->assertJsonMissingPath('data.raw_response');
    }

    public function test_failed_gateway_call_persists_payment_with_failed_status(): void
    {
        $user = $this->user();
        $this->createMethod('midtrans_va_bca', 'midtrans');
        $order = $this->createOrder($user);

        $gatewayMock = $this->mock(PaymentGatewayInterface::class);
        $gatewayMock->shouldReceive('createPayment')
            ->once()
            ->andThrow(new GatewayException('Connection timeout'));

        $factoryMock = $this->mock(PaymentGatewayFactory::class);
        $factoryMock->shouldReceive('make')
            ->with('midtrans')
            ->andReturn($gatewayMock);

        $response = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'midtrans_va_bca',
        ]);

        $response->assertStatus(503);

        // Verify the payment record was persisted with failed status (not rolled back!)
        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'failed',
        ]);
    }

    public function test_payment_creation_is_idempotent_and_capped_by_order_expiry(): void
    {
        $user = $this->user();
        $this->createMethod();
        $order = $this->createOrder($user, ['expired_at' => now()->addHour()]);
        $key = (string) Str::uuid();

        $first = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'manual_bca',
            'idempotency_key' => $key,
        ])->assertCreated();
        $second = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'manual_bca',
            'idempotency_key' => $key,
        ])->assertCreated();

        $this->assertSame($first->json('data.id'), $second->json('data.id'));
        $this->assertDatabaseCount('payments', 1);
        $this->assertTrue(Payment::query()->firstOrFail()->expired_at->lte($order->expired_at));
    }

    public function test_midtrans_creates_bca_va_using_payment_id_and_order_bounded_expiry(): void
    {
        Config::set('services.midtrans.server_key', 'test-server-key');
        Config::set('services.midtrans.base_url', 'https://api.sandbox.midtrans.com');
        $user = $this->user();
        $this->createMethod('midtrans_va_bca', 'midtrans');
        $order = $this->createOrder($user, ['expired_at' => now()->addHour()]);
        Http::fake(function (Request $request) {
            $response = [
                'status_code' => '201',
                'transaction_id' => 'midtrans-transaction-123',
                'order_id' => $request['transaction_details']['order_id'],
                'gross_amount' => '200000.00',
                'payment_type' => 'bank_transfer',
                'currency' => 'IDR',
                'transaction_status' => 'pending',
                'expiry_time' => now()->addHours(2)->toISOString(),
                'va_numbers' => [[
                    'bank' => 'bca',
                    'va_number' => '1234567890123456',
                ]],
            ];

            return Http::response($response);
        });

        $response = $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'midtrans_va_bca',
        ])->assertCreated();

        $paymentId = $response->json('data.id');
        $response->assertJsonPath('data.instructions.bank', 'BCA')
            ->assertJsonPath('data.instructions.va_number', '1234567890123456')
            ->assertJsonPath('data.payment_url', null);
        Http::assertSent(fn (Request $request) => $request['transaction_details']['order_id'] === $paymentId
            && $request['transaction_details']['gross_amount'] === 200000
            && $request['bank_transfer']['bank'] === 'bca'
            && $request['custom_expiry']['expiry_duration'] <= 3600
            && $request->hasHeader('Authorization', 'Basic '.base64_encode('test-server-key:')));
        $this->assertTrue(Payment::query()->findOrFail($paymentId)->expired_at->lte($order->expired_at));
    }

    public function test_unsupported_gateway_cannot_create_new_payment(): void
    {
        $user = $this->user();
        $this->createMethod('unsupported_method', 'unsupported');
        $order = $this->createOrder($user);

        $this->actingAs($user)->postJson("/api/orders/{$order->id}/payments", [
            'payment_method_code' => 'unsupported_method',
        ])->assertUnprocessable();

        $this->assertDatabaseCount('payments', 0);
    }
}
