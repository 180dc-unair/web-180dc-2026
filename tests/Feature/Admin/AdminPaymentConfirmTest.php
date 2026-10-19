<?php

namespace Tests\Feature\Admin;

use App\Models\MediaAsset;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class AdminPaymentConfirmTest extends TestCase
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

    private function payment(string $gateway, bool $withProof = true): Payment
    {
        $product = Product::query()->create([
            'title' => 'Kaos',
            'slug' => 'kaos-'.Str::uuid(),
            'type' => 'physical',
            'status' => 'active',
            'price' => 100000,
            'stock' => 5,
        ]);
        $order = Order::query()->create([
            'order_number' => 'ORD-'.strtoupper(Str::random(8)),
            'status' => 'pending',
            'customer_name' => 'Customer',
            'customer_email' => 'cust@example.com',
            'subtotal_amount' => 100000,
            'total_amount' => 100000,
            'currency' => 'IDR',
            'expired_at' => now()->addHours(24),
        ]);
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'item_type' => 'product',
            'name' => $product->title,
            'quantity' => 1,
            'unit_price' => $product->price,
            'total_price' => bcmul((string) $product->price, '1', 2),
        ]);

        $method = PaymentMethod::query()->create([
            'name' => 'Manual BCA',
            'code' => 'manual_bca',
            'gateway' => $gateway,
            'is_active' => true,
        ]);

        $proofId = null;
        if ($withProof) {
            $proofId = MediaAsset::query()->create([
                'file_id' => 'proof-'.Str::random(8),
                'url' => 'https://ik.imagekit.io/180dc/proof.jpg',
            ])->id;
        }

        return Payment::query()->create([
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'gateway' => $gateway,
            'gateway_reference' => 'ref-'.Str::random(8),
            'status' => 'pending',
            'amount' => 100000,
            'proof_media_id' => $proofId,
        ]);
    }

    public function test_admin_can_confirm_manual_payment_with_proof(): void
    {
        $admin = $this->admin();
        $payment = $this->payment('manual');

        $this->actingAs($admin)
            ->postJson("/api/admin/payments/{$payment->id}/confirm")
            ->assertOk()
            ->assertJsonPath('data.status', 'paid')
            ->assertJsonPath('data.gateway', 'manual');

        $this->assertSame('paid', $payment->fresh()->status);
        $this->assertNotNull($payment->fresh()->paid_at);
    }

    public function test_cannot_confirm_payment_without_proof(): void
    {
        $admin = $this->admin();
        $payment = $this->payment('manual', false);

        $this->actingAs($admin)
            ->postJson("/api/admin/payments/{$payment->id}/confirm")
            ->assertStatus(409);
    }

    public function test_cannot_confirm_midtrans_payment_locally(): void
    {
        $admin = $this->admin();
        $payment = $this->payment('midtrans');

        $this->actingAs($admin)
            ->postJson("/api/admin/payments/{$payment->id}/confirm")
            ->assertStatus(409);
    }
}
