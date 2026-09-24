<?php

namespace App\Services;

use App\Exceptions\GatewayException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PaymentMethodRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use App\Repositories\Contracts\ProductRepositoryInterface;
use App\Services\Payments\PaymentGatewayFactory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class PaymentService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly ProductRepositoryInterface $productRepository,
        private readonly PaymentGatewayFactory $gatewayFactory,
        private readonly ImageKitMediaService $mediaService,
    ) {
        //
    }

    /** @param array{payment_method_code: string, idempotency_key?: string} $data */
    public function create(Order $order, array $data): Payment
    {
        [$payment, $lockedOrder, $method, $isNew] = DB::transaction(function () use ($order, $data) {
            $lockedOrder = $this->orderRepository->findByIdForUpdate($order->id);
            abort_unless($lockedOrder, 404, 'Order not found.');

            $key = $data['idempotency_key'] ?? null;
            if ($key && ($existing = $this->paymentRepository->findByIdempotencyKey($lockedOrder->id, $key))) {
                $existing->load('method');
                abort_if($existing->method?->code !== $data['payment_method_code'], 409, 'Idempotency key was used with another payment method.');

                return [$existing, $lockedOrder, $existing->method, false];
            }

            $this->assertPayable($lockedOrder);
            $method = $this->paymentMethodRepository->findByCode($data['payment_method_code']);
            abort_if(! $method || ! $method->is_active, 422, 'Payment method is not available.');
            abort_unless(in_array($method->gateway, ['manual', 'midtrans'], true), 422, 'Payment method is not available.');
            abort_if($this->paymentRepository->findPendingByOrder($lockedOrder->id), 409, 'Payment already pending. Cancel first or wait for expiry.');

            $expiry = now()->addHours((int) config('services.payments.ttl_hours', 24))->min($lockedOrder->expired_at);
            $payment = $this->paymentRepository->create([
                'order_id' => $lockedOrder->id,
                'payment_method_id' => $method->id,
                'gateway' => $method->gateway,
                'idempotency_key' => $key,
                'status' => 'pending',
                'amount' => $lockedOrder->total_amount,
                'expired_at' => $expiry,
            ]);

            return [$payment, $lockedOrder, $method, true];
        }, 3);

        if (! $isNew) {
            return $payment->load('method');
        }

        try {
            $result = $this->gatewayFactory->make($method->gateway)->createPayment($lockedOrder, $payment, $method);
        } catch (GatewayException $exception) {
            DB::transaction(function () use ($payment, $exception) {
                $this->orderRepository->findByIdForUpdate($payment->order_id);
                $locked = $this->paymentRepository->findById($payment->id, true);
                if ($locked?->status === 'pending' && ! $locked->gateway_reference) {
                    $this->paymentRepository->update($locked, [
                        'status' => 'failed',
                        'raw_response' => ['creation_error' => $exception->getMessage()],
                    ]);
                }
            }, 3);
            Log::error('gateway.create.failed', ['payment_id' => $payment->id, 'gateway' => $method->gateway]);
            abort(503, 'Payment gateway error.');
        }

        return DB::transaction(function () use ($payment, $result) {
            $order = $this->orderRepository->findByIdForUpdate($payment->order_id);
            $locked = $this->paymentRepository->findById($payment->id, true);
            abort_unless($order && $locked, 404, 'Payment not found.');
            $this->assertPayable($order);
            abort_if($locked->status !== 'pending', 409, 'Payment is no longer pending.');
            $expiry = Carbon::parse($result['expired_at'])->min($locked->expired_at)->min($order->expired_at);

            return $this->paymentRepository->update($locked, [
                'gateway_reference' => $result['gateway_reference'],
                'payment_url' => $result['payment_url'],
                'raw_response' => array_merge($locked->raw_response ?? [], ['creation' => $result['raw_response']]),
                'expired_at' => $expiry,
            ])->load('method');
        }, 3);
    }

    /** @param array{gateway: string, gateway_reference: string, status: string, amount: string, paid_at?: mixed} $data */
    public function settlePayment(Order $order, Payment $payment, array $data): Payment
    {
        abort_if(
            $payment->gateway !== $data['gateway']
            || bccomp($payment->amount, $data['amount'], 2) !== 0
            || bccomp($payment->amount, $order->total_amount, 2) !== 0,
            409,
            'Payment gateway or amount does not match.'
        );
        if ($payment->gateway_reference === null && isset($data['gateway_reference'])) {
            $payment = $this->paymentRepository->update($payment, [
                'gateway_reference' => $data['gateway_reference'],
            ]);
        }
        abort_if($payment->gateway_reference !== $data['gateway_reference'], 409, 'Gateway reference does not match.');

        if ($payment->status === 'paid' || $payment->status === $data['status'] || $data['status'] === 'pending') {
            return $payment;
        }

        abort_if($payment->status !== 'pending', 409, 'Payment is no longer pending.');
        if ($data['status'] !== 'paid') {
            return $this->paymentRepository->update($payment, ['status' => $data['status']]);
        }

        $this->assertPayable($order);
        abort_if(! $payment->expired_at || $payment->expired_at->lte(now()), 409, 'Payment has expired.');

        $order->load('items');
        $quantities = [];
        foreach ($order->items as $item) {
            if ($item->item_type === 'product') {
                abort_unless($item->product_id, 409, 'Ordered product no longer exists.');
                $quantities[$item->product_id] = ($quantities[$item->product_id] ?? 0) + $item->quantity;
            }
        }
        $products = $this->productRepository->lockForSale(array_keys($quantities));
        foreach ($quantities as $id => $quantity) {
            $product = $products->get($id);
            abort_if(! $product || ($product->type === 'physical' && $product->stock < $quantity), 409, 'Insufficient stock during payment settlement.');
        }
        foreach ($quantities as $id => $quantity) {
            $this->productRepository->recordSale($products->get($id), $quantity);
        }

        $paidAt = $data['paid_at'] ?? now();
        $this->orderRepository->update($order, ['status' => 'paid', 'paid_at' => $paidAt]);

        return $this->paymentRepository->update($payment, [
            'status' => 'paid',
            'paid_at' => $paidAt,
            'raw_response' => array_merge($payment->raw_response ?? [], ['settlement' => $data]),
        ]);
    }

    public function uploadProof(Payment $payment, UploadedFile $proof): Payment
    {
        $this->assertProofUploadable($payment);
        abort_unless(config('services.imagekit.private_key'), 503, 'Payment proof upload is not configured.');

        try {
            $media = $this->mediaService->upload($proof, 'payment-proofs');
        } catch (Throwable $exception) {
            report($exception);
            abort(502, 'Payment proof upload failed. Please try again.');
        }

        return DB::transaction(function () use ($payment, $media) {
            $order = $this->orderRepository->findByIdForUpdate($payment->order_id);
            $locked = $this->paymentRepository->findById($payment->id, true);
            abort_unless($order && $locked, 404, 'Payment not found.');
            $this->assertPayable($order);
            $this->assertProofUploadable($locked);

            return $this->paymentRepository->update($locked, ['proof_media_id' => $media->id])
                ->load(['method', 'proof']);
        }, 3);
    }

    public function findByIdForUser(string $id, User $user): ?Payment
    {
        return $user->role === 'admin'
            ? $this->paymentRepository->findById($id)
            : $this->paymentRepository->findByIdAndUser($id, $user->id);
    }

    /** @return Collection<int, Payment> */
    public function allByOrder(string $orderId): Collection
    {
        return $this->paymentRepository->allByOrder($orderId);
    }

    public function cancel(Payment $payment): Payment
    {
        return DB::transaction(function () use ($payment) {
            $this->orderRepository->findByIdForUpdate($payment->order_id);
            $locked = $this->paymentRepository->findById($payment->id, true);
            abort_unless($locked, 404, 'Payment not found.');
            abort_if($locked->status !== 'pending', 409, "Cannot cancel payment with status '{$locked->status}'.");
            abort_if($locked->gateway !== 'manual', 409, 'Midtrans payments cannot be cancelled locally.');

            return $this->paymentRepository->update($locked, ['status' => 'cancelled']);
        }, 3);
    }

    /** @param array<string, mixed> $filters */
    public function listAll(array $filters = []): LengthAwarePaginator
    {
        return $this->paymentRepository->paginateAll($filters);
    }

    public function expirePending(): int
    {
        return $this->paymentRepository->expirePending();
    }

    private function assertPayable(Order $order): void
    {
        abort_if($order->status !== 'pending' || ! $order->expired_at || $order->expired_at->lte(now()), 409, 'Order is not pending or has expired.');
    }

    private function assertProofUploadable(Payment $payment): void
    {
        abort_if($payment->gateway !== 'manual' || ! $payment->gateway_reference, 409, 'Only initialized manual payments accept proof uploads.');
        abort_if($payment->status !== 'pending', 409, 'Payment is no longer pending.');
        abort_if(! $payment->expired_at || $payment->expired_at->lte(now()), 409, 'Payment has expired.');
        abort_if($payment->proof_media_id, 409, 'Payment proof has already been uploaded.');
    }
}
