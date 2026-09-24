<?php

namespace App\Services\Payments\Gateways;

use App\Exceptions\GatewayException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payments\Contracts\PaymentGatewayInterface;

class ManualGateway implements PaymentGatewayInterface
{
    public function createPayment(Order $order, Payment $payment, PaymentMethod $method): array
    {
        $instructions = config('services.payments.manual', []);
        if (empty($instructions['bank']) || empty($instructions['account_number']) || empty($instructions['account_name'])) {
            throw new GatewayException('Manual bank instructions are not configured.');
        }

        return [
            'gateway_reference' => 'MANUAL-'.$order->order_number.'-'.$payment->id,
            'payment_url' => null,
            'raw_response' => [
                'bank' => $instructions['bank'],
                'account_number' => $instructions['account_number'],
                'account_name' => $instructions['account_name'],
                'note' => 'Transfer sesuai total_amount. Sertakan order number di berita transfer.',
            ],
            'expired_at' => $payment->expired_at,
        ];
    }

    public function checkStatus(Payment $payment): array
    {
        return ['status' => $payment->status];
    }

    public function parseWebhook(array $payload): array
    {
        throw new GatewayException('Manual confirmation requires admin authentication.');
    }

    public function verifySignature(array $headers, string $rawBody): bool
    {
        // Manual payments cannot be confirmed via public webhook endpoints.
        // They must be confirmed by an administrator via POST /api/admin/payments/{payment}/confirm.
        return false;
    }
}
