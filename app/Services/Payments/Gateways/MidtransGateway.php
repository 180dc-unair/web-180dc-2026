<?php

namespace App\Services\Payments\Gateways;

use App\Exceptions\GatewayException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Services\Payments\Contracts\PaymentGatewayInterface;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Validator;

class MidtransGateway implements PaymentGatewayInterface
{
    public function createPayment(Order $order, Payment $payment, PaymentMethod $method): array
    {
        $serverKey = $this->serverKey();
        if ($serverKey === '') {
            throw new GatewayException('Midtrans credentials are not configured.');
        }

        if (bccomp($payment->amount, (string) (int) $payment->amount, 2) !== 0) {
            throw new GatewayException('Midtrans requires a whole-rupiah amount.');
        }

        $duration = $payment->expired_at->getTimestamp() - now()->getTimestamp();
        if ($duration < 20) {
            throw new GatewayException('Payment expiry is too close for Midtrans.');
        }

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(15)
                ->post($this->baseUrl().'/v2/charge', [
                    'payment_type' => 'bank_transfer',
                    'transaction_details' => [
                        'order_id' => $payment->id,
                        'gross_amount' => (int) $payment->amount,
                    ],
                    'bank_transfer' => ['bank' => 'bca'],
                    'custom_expiry' => [
                        'expiry_duration' => $duration,
                        'unit' => 'second',
                    ],
                ]);
        } catch (ConnectionException) {
            throw new GatewayException('Midtrans request outcome is uncertain.');
        }

        if (! $response->successful()) {
            throw new GatewayException('Midtrans charge creation failed.');
        }

        $data = $response->json();
        $va = is_array($data) ? ($data['va_numbers'][0] ?? null) : null;
        if (! is_array($data) || ! is_string($data['transaction_id'] ?? null)
            || ($data['order_id'] ?? null) !== $payment->id
            || ($data['transaction_status'] ?? null) !== 'pending'
            || ($data['payment_type'] ?? null) !== 'bank_transfer'
            || ($data['currency'] ?? null) !== 'IDR'
            || ! is_numeric($data['gross_amount'] ?? null)
            || bccomp((string) $data['gross_amount'], $payment->amount, 2) !== 0
            || ! is_array($va) || ($va['bank'] ?? null) !== 'bca'
            || ! is_string($va['va_number'] ?? null) || $va['va_number'] === '') {
            throw new GatewayException('Midtrans returned an invalid charge response.');
        }

        try {
            $expiry = isset($data['expiry_time'])
                ? Carbon::parse($data['expiry_time'], 'Asia/Jakarta')
                : $payment->expired_at;
        } catch (\Throwable) {
            throw new GatewayException('Midtrans returned an invalid expiry date.');
        }

        return [
            'gateway_reference' => $data['transaction_id'],
            'payment_url' => null,
            'raw_response' => $data,
            'expired_at' => $expiry->min($payment->expired_at),
        ];
    }

    public function checkStatus(Payment $payment): array
    {
        $serverKey = $this->serverKey();
        if ($serverKey === '') {
            throw new GatewayException('Midtrans credentials are not configured.');
        }

        try {
            $response = Http::withBasicAuth($serverKey, '')
                ->acceptJson()
                ->connectTimeout(5)
                ->timeout(15)
                ->get($this->baseUrl().'/v2/'.rawurlencode($payment->id).'/status');
        } catch (ConnectionException) {
            throw new GatewayException('Midtrans status request failed.');
        }

        if (! $response->successful() || ! is_string($response->json('transaction_status'))) {
            throw new GatewayException('Midtrans status request failed.');
        }

        return ['status' => $this->mapStatus($response->json('transaction_status'))];
    }

    public function parseWebhook(array $payload): array
    {
        $data = Validator::make($payload, [
            'transaction_id' => ['required', 'string', 'max:255'],
            'order_id' => ['required', 'uuid'],
            'transaction_status' => ['required', 'in:pending,capture,settlement,deny,cancel,expire,failure'],
            'status_code' => ['required', 'string', 'max:3'],
            'gross_amount' => ['required', 'numeric', 'min:0', 'regex:/^\d+(?:\.\d{1,2})?$/'],
            'payment_type' => ['required', 'in:bank_transfer'],
            'currency' => ['required', 'in:IDR'],
            'fraud_status' => ['nullable', 'string', 'in:accept,challenge,deny'],
            'settlement_time' => ['nullable', 'date'],
            'transaction_time' => ['nullable', 'date'],
        ])->validate();

        $paidAt = $data['settlement_time'] ?? $data['transaction_time'] ?? null;

        $mappedStatus = $this->mapStatus($data['transaction_status']);

        if ($mappedStatus === 'paid') {
            if ($data['status_code'] !== '200') {
                $mappedStatus = 'pending';
            }
            if (isset($data['fraud_status']) && $data['fraud_status'] !== 'accept') {
                $mappedStatus = $data['fraud_status'] === 'challenge' ? 'pending' : 'failed';
            }
        }

        return [
            'gateway_reference' => $data['transaction_id'],
            'payment_id' => $data['order_id'],
            'status' => $mappedStatus,
            'amount' => (string) $data['gross_amount'],
            'paid_at' => $paidAt ? Carbon::parse($paidAt, 'Asia/Jakarta')->toISOString() : null,
        ];
    }

    public function verifySignature(array $headers, string $rawBody): bool
    {
        $serverKey = $this->serverKey();
        if ($serverKey === '') {
            return false;
        }

        try {
            $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            return false;
        }

        if (! is_array($payload)) {
            return false;
        }

        foreach (['order_id', 'status_code', 'gross_amount', 'signature_key'] as $field) {
            if (! is_string($payload[$field] ?? null)) {
                return false;
            }
        }

        $signature = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);

        return hash_equals($signature, $payload['signature_key']);
    }

    private function serverKey(): string
    {
        return (string) config('services.midtrans.server_key', '');
    }

    private function baseUrl(): string
    {
        return rtrim((string) config('services.midtrans.base_url', 'https://api.sandbox.midtrans.com'), '/');
    }

    private function mapStatus(string $status): string
    {
        return match (strtolower($status)) {
            'capture', 'settlement' => 'paid',
            'pending' => 'pending',
            'expire' => 'expired',
            'cancel' => 'cancelled',
            default => 'failed',
        };
    }
}
