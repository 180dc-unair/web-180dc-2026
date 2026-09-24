<?php

namespace App\Services\Payments\Contracts;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Carbon\Carbon;

interface PaymentGatewayInterface
{
    /**
     * Create a payment via the gateway.
     *
     * @return array{gateway_reference: string, payment_url: ?string, raw_response: array, expired_at: Carbon}
     */
    public function createPayment(Order $order, Payment $payment, PaymentMethod $method): array;

    /**
     * Check payment status (polling fallback jika webhook delay).
     *
     * @return array{status: string}
     */
    public function checkStatus(Payment $payment): array;

    /**
     * Parse webhook payload to normalized shape.
     *
     * @return array{gateway_reference: string, status: string, amount: ?string, paid_at: ?string}
     */
    public function parseWebhook(array $payload): array;

    /**
     * Verify webhook signature from the gateway.
     */
    public function verifySignature(array $headers, string $rawBody): bool;
}
