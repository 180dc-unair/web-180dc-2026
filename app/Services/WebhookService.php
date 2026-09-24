<?php

namespace App\Services;

use App\Models\Payment;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\PaymentWebhookRepositoryInterface;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class WebhookService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PaymentWebhookRepositoryInterface $webhookRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly PaymentGatewayFactory $gatewayFactory,
        private readonly PaymentService $paymentService,
    ) {
        //
    }

    /** @param array<string, array<int, string>> $headers */
    public function handle(string $gateway, array $headers, string $rawBody): array
    {
        $adapter = $this->gatewayFactory->make($gateway);
        abort_unless($adapter->verifySignature($headers, $rawBody), 403, 'Invalid signature.');

        try {
            $payload = json_decode($rawBody, true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException) {
            abort(422, 'Invalid JSON payload.');
        }
        abort_unless(is_array($payload), 422, 'Invalid webhook payload.');

        $webhook = $this->webhookRepository->create([
            'payment_id' => null,
            'gateway' => $gateway,
            'payload' => $payload,
            'is_processed' => false,
        ]);

        try {
            $data = $adapter->parseWebhook($payload);
            $payment = $this->paymentRepository->findByReference($data['gateway_reference'], $data['payment_id']);
            if (! $payment) {
                return ['status' => 'ignored'];
            }
            abort_if($payment->id !== $data['payment_id'] || $payment->gateway !== $gateway, 409, 'Webhook payment mapping does not match.');
            $this->webhookRepository->update($webhook, ['payment_id' => $payment->id]);

            DB::transaction(function () use ($gateway, $payment, $data, $webhook) {
                $order = $this->orderRepository->findByIdForUpdate($payment->order_id);
                $locked = $this->paymentRepository->findById($payment->id, true);
                abort_unless($order && $locked, 409, 'Webhook payment not found.');
                $this->paymentService->settlePayment($order, $locked, array_merge($data, ['gateway' => $gateway]));
                $this->webhookRepository->update($webhook, ['is_processed' => true, 'processing_error' => null]);
            }, 3);
        } catch (Throwable $exception) {
            $message = $exception instanceof HttpExceptionInterface ? $exception->getMessage() : 'Webhook processing failed.';
            $this->webhookRepository->update($webhook, ['processing_error' => $message]);
            Log::error('webhook.processing.failed', ['webhook_id' => $webhook->id, 'gateway' => $gateway, 'error' => $message]);
            throw $exception;
        }

        return ['status' => 'ok'];
    }

    public function confirmManual(User $admin, Payment $payment): Payment
    {
        abort_if($payment->gateway !== 'manual' || ! $payment->gateway_reference, 409, 'Only initialized manual payments can be confirmed.');
        abort_unless($payment->proof_media_id, 409, 'Payment proof is required before confirmation.');

        $data = [
            'gateway' => 'manual',
            'gateway_reference' => $payment->gateway_reference,
            'status' => 'paid',
            'amount' => $payment->amount,
            'paid_at' => now()->toISOString(),
            'confirmed_by' => $admin->id,
        ];
        $webhook = $this->webhookRepository->create([
            'payment_id' => $payment->id,
            'gateway' => 'manual',
            'payload' => $data,
            'is_processed' => false,
        ]);

        try {
            return DB::transaction(function () use ($payment, $data, $webhook) {
                $order = $this->orderRepository->findByIdForUpdate($payment->order_id);
                $locked = $this->paymentRepository->findById($payment->id, true);
                abort_unless($order && $locked, 404, 'Payment not found.');
                abort_unless($locked->proof_media_id, 409, 'Payment proof is required before confirmation.');
                $updated = $this->paymentService->settlePayment($order, $locked, $data);
                $this->webhookRepository->update($webhook, ['is_processed' => true, 'processing_error' => null]);

                return $updated->load(['method', 'proof']);
            }, 3);
        } catch (Throwable $exception) {
            $message = $exception instanceof HttpExceptionInterface ? $exception->getMessage() : 'Manual confirmation failed.';
            $this->webhookRepository->update($webhook, ['processing_error' => $message]);
            Log::error('payment.manual.confirmation.failed', ['webhook_id' => $webhook->id, 'payment_id' => $payment->id, 'error' => $message]);
            throw $exception;
        }
    }
}
