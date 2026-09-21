<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class WebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.midtrans.server_key', 'midtrans-server-key');
    }

    private function user(): User
    {
        return User::query()->create([
            'role' => 'user',
            'name' => 'Webhook User',
            'username' => 'user-'.Str::random(8),
            'email' => 'user-'.Str::random(8).'@example.com',
            'password' => Hash::make('password'),
        ]);
    }

    /** @return array{Order, Payment, Product} */
    private function pendingPayment(string $productType = 'physical'): array
    {
        $user = $this->user();
        $method = PaymentMethod::query()->create([
            'name' => 'Midtrans VA BCA',
            'code' => 'midtrans_va_bca_'.Str::lower(Str::random(6)),
            'gateway' => 'midtrans',
            'is_active' => true,
        ]);
        $order = Order::query()->create([
            'user_id' => $user->id,
            'order_number' => 'ORD-MIDTRANS-'.strtoupper(Str::random(8)),
            'status' => 'pending',
            'customer_name' => $user->name,
            'customer_email' => $user->email,
            'subtotal_amount' => 100000,
            'total_amount' => 100000,
            'currency' => 'IDR',
            'expired_at' => now()->addHour(),
        ]);
        $product = Product::query()->create([
            'title' => 'Payment Product '.Str::random(6),
            'slug' => 'payment-product-'.Str::lower(Str::random(8)),
            'type' => $productType,
            'status' => 'active',
            'price' => 100000,
            'stock' => 10,
            'sold_count' => 2,
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'item_type' => 'product',
            'name' => $product->title,
            'quantity' => 1,
            'unit_price' => 100000,
            'total_price' => 100000,
        ]);
        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'gateway' => 'midtrans',
            'status' => 'pending',
            'amount' => 100000,
            'gateway_reference' => (string) Str::uuid(),
            'expired_at' => now()->addHour(),
        ]);

        return [$order, $payment, $product];
    }

    /** @param array<string, mixed> $overrides */
    private function signedPayload(Payment $payment, array $overrides = []): array
    {
        $payload = array_merge([
            'transaction_id' => $payment->gateway_reference,
            'order_id' => $payment->id,
            'transaction_status' => 'settlement',
            'status_code' => '200',
            'gross_amount' => $payment->amount,
            'payment_type' => 'bank_transfer',
            'currency' => 'IDR',
            'settlement_time' => now()->format('Y-m-d H:i:s'),
        ], $overrides);
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].config('services.midtrans.server_key'),
        );

        return $payload;
    }

    public function test_webhook_invalid_signature_returns_403(): void
    {
        [, $payment] = $this->pendingPayment();
        $payload = $this->signedPayload($payment);
        $payload['signature_key'] = 'invalid-signature';

        $this->postJson('/api/webhooks/midtrans', $payload)->assertForbidden();
    }

    public function test_webhook_for_unknown_payment_is_ignored(): void
    {
        $payment = new Payment([
            'gateway_reference' => (string) Str::uuid(),
            'amount' => '100000.00',
        ]);
        $payment->id = (string) Str::uuid();

        $this->postJson('/api/webhooks/midtrans', $this->signedPayload($payment))
            ->assertOk()
            ->assertJsonPath('status', 'ignored');

        $this->assertDatabaseHas('payment_webhooks', [
            'payment_id' => null,
            'gateway' => 'midtrans',
            'is_processed' => false,
            'processing_error' => null,
        ]);
    }

    public function test_midtrans_webhook_paid_updates_order_and_stock(): void
    {
        [$order, $payment, $product] = $this->pendingPayment();

        $this->postJson('/api/webhooks/midtrans', $this->signedPayload($payment))
            ->assertOk()
            ->assertJsonPath('status', 'ok');

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertSame('paid', $order->fresh()->status);
        $this->assertSame(9, $product->fresh()->stock);
        $this->assertSame(3, $product->fresh()->sold_count);
        $this->assertDatabaseHas('payment_webhooks', [
            'payment_id' => $payment->id,
            'gateway' => 'midtrans',
            'is_processed' => true,
        ]);
    }

    public function test_webhook_duplicate_paid_is_idempotent(): void
    {
        [, $payment, $product] = $this->pendingPayment();
        $payload = $this->signedPayload($payment);

        $this->postJson('/api/webhooks/midtrans', $payload)->assertOk();
        $this->postJson('/api/webhooks/midtrans', $payload)->assertOk();

        $this->assertSame(9, $product->fresh()->stock);
        $this->assertSame(3, $product->fresh()->sold_count);
    }

    public function test_webhook_rejects_underpaid_amount(): void
    {
        [$order, $payment] = $this->pendingPayment();

        $this->postJson('/api/webhooks/midtrans', $this->signedPayload($payment, [
            'gross_amount' => '50000.00',
        ]))->assertStatus(409);

        $this->assertSame('pending', $payment->fresh()->status);
        $this->assertSame('pending', $order->fresh()->status);
        $this->assertDatabaseHas('payment_webhooks', [
            'payment_id' => $payment->id,
            'is_processed' => false,
            'processing_error' => 'Payment gateway or amount does not match.',
        ]);
    }

    public function test_midtrans_expire_marks_payment_expired(): void
    {
        [, $payment] = $this->pendingPayment();

        $this->postJson('/api/webhooks/midtrans', $this->signedPayload($payment, [
            'transaction_status' => 'expire',
            'status_code' => '202',
            'settlement_time' => null,
        ]))->assertOk();

        $this->assertSame('expired', $payment->fresh()->status);
    }

    public function test_midtrans_non_paid_statuses_are_normalized_and_idempotent(): void
    {
        foreach ([
            'pending' => 'pending',
            'deny' => 'failed',
            'cancel' => 'cancelled',
            'failure' => 'failed',
        ] as $gatewayStatus => $expectedStatus) {
            [, $payment] = $this->pendingPayment();

            $payload = $this->signedPayload($payment, [
                'transaction_status' => $gatewayStatus,
                'status_code' => $gatewayStatus === 'pending' ? '201' : '202',
                'settlement_time' => null,
            ]);

            $this->postJson('/api/webhooks/midtrans', $payload)->assertOk();
            $this->postJson('/api/webhooks/midtrans', $payload)->assertOk();

            $this->assertSame($expectedStatus, $payment->fresh()->status);
        }
    }

    public function test_webhook_does_not_decrement_stock_for_digital_products(): void
    {
        [, $payment, $product] = $this->pendingPayment('digital');

        $this->postJson('/api/webhooks/midtrans', $this->signedPayload($payment))->assertOk();

        $this->assertSame(10, $product->fresh()->stock);
        $this->assertSame(3, $product->fresh()->sold_count);
    }

    public function test_manual_webhook_is_not_accessible_via_public_route(): void
    {
        $this->postJson('/api/webhooks/manual', [
            'gateway_reference' => 'MANUAL-123',
            'status' => 'paid',
        ])->assertNotFound();
    }
}
