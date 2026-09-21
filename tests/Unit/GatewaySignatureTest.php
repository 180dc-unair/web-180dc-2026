<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Services\Payments\Gateways\ManualGateway;
use App\Services\Payments\Gateways\MidtransGateway;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GatewaySignatureTest extends TestCase
{
    public function test_midtrans_signature_verification_fails_closed(): void
    {
        $gateway = new MidtransGateway;
        Config::set('services.midtrans.server_key', 'my-midtrans-server-key');
        $payload = [
            'order_id' => '01994c5d-5d4d-72f2-bf04-b85f205a071a',
            'status_code' => '200',
            'gross_amount' => '100000.00',
        ];
        $payload['signature_key'] = hash(
            'sha512',
            $payload['order_id'].$payload['status_code'].$payload['gross_amount'].'my-midtrans-server-key',
        );

        $this->assertTrue($gateway->verifySignature([], json_encode($payload, JSON_THROW_ON_ERROR)));
        $payload['signature_key'] = 'wrong-signature';
        $this->assertFalse($gateway->verifySignature([], json_encode($payload, JSON_THROW_ON_ERROR)));
        $this->assertFalse($gateway->verifySignature([], 'not-json'));

        Config::set('services.midtrans.server_key', '');
        $this->assertFalse($gateway->verifySignature([], json_encode($payload, JSON_THROW_ON_ERROR)));
    }

    public function test_manual_gateway_rejects_public_webhooks(): void
    {
        $this->assertFalse((new ManualGateway)->verifySignature([], ''));
    }

    public function test_midtrans_status_is_normalized(): void
    {
        Config::set('services.midtrans.server_key', 'my-midtrans-server-key');
        Config::set('services.midtrans.base_url', 'https://api.sandbox.midtrans.com');
        Http::fake([
            'api.sandbox.midtrans.com/*' => Http::response(['transaction_status' => 'settlement']),
        ]);
        $payment = new Payment;
        $payment->id = '01994c5d-5d4d-72f2-bf04-b85f205a071a';

        $this->assertSame(['status' => 'paid'], (new MidtransGateway)->checkStatus($payment));
    }
}
