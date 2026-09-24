<?php

namespace App\Services;

use App\Models\Event;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\OrderItemRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orderRepository,
        private readonly OrderItemRepositoryInterface $orderItemRepository,
        private readonly CartService $cartService,
        private readonly OrderNumberService $orderNumberService,
        private readonly PaymentRepositoryInterface $paymentRepository,
    ) {
        //
    }

    public function prepareOrder(Order $order): Order
    {
        $isExpired = $order->expired_at ? $order->expired_at->isPast() : false;
        $order->is_expired = $isExpired;
        $order->can_cancel = $order->status === 'pending' && ! $isExpired;
        $order->can_pay = $order->status === 'pending' && ! $isExpired;

        return $order;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function checkout(User $user, array $data): Order
    {
        // --- Cek idempotency key (jika dikirim) ---
        $idempotencyKey = $data['idempotency_key'] ?? null;

        if ($idempotencyKey) {
            $existingOrder = $this->orderRepository->findByIdempotencyKey($idempotencyKey, $user->id);

            if ($existingOrder) {
                $existingOrder->load(['items.product.image', 'items.event.image']);

                return $this->prepareOrder($existingOrder);
            }
        }

        try {
            return DB::transaction(function () use ($user, $data, $idempotencyKey) {
                $cart = $this->cartService->getCurrentCart($user);

                // --- Tentukan source items ---
                if (! empty($data['items'])) {
                    // Mode B: Direct items (dari body request, untuk event/buy-now)
                    $sourceItems = $data['items'];
                } else {
                    // Mode A: Dari cart (paling umum)
                    $cartItems = $cart->items()->with('product')->get();

                    if ($cartItems->isEmpty()) {
                        throw ValidationException::withMessages([
                            'cart' => ['Cart is empty.'],
                        ]);
                    }

                    $sourceItems = $cartItems->map(fn ($ci) => [
                        'item_type' => 'product',
                        'product_id' => $ci->product_id,
                        'quantity' => $ci->quantity,
                    ])->toArray();
                }

                // --- Validasi & snapshot setiap item ---
                $snapshots = [];
                $subtotal = '0.00';

                foreach ($sourceItems as $item) {
                    $itemType = $item['item_type'] ?? 'product';

                    if ($itemType === 'event') {
                        $event = Event::query()->lockForUpdate()->findOrFail($item['event_id']);

                        if ($event->status !== 'active') {
                            abort(response()->json([
                                'status' => 'error',
                                'message' => 'Event not available.',
                                'errors' => ['event_id' => ["Event '{$event->title}' is not active."]],
                            ], 409));
                        }

                        $totalPrice = bcmul((string) $event->price, (string) $item['quantity'], 2);

                        $snapshots[] = [
                            'product_id' => null,
                            'event_id' => $event->id,
                            'item_type' => 'event',
                            'name' => $event->title,
                            'quantity' => $item['quantity'],
                            'unit_price' => $event->price,
                            'total_price' => $totalPrice,
                        ];

                        $subtotal = bcadd($subtotal, $totalPrice, 2);
                    } else {
                        // lockForUpdate → mencegah 2 checkout paralel pada stok terakhir
                        $product = Product::query()->lockForUpdate()->findOrFail($item['product_id']);

                        if ($product->status !== 'active') {
                            abort(response()->json([
                                'status' => 'error',
                                'message' => 'Product not available.',
                                'errors' => ['product_id' => ["Product '{$product->title}' is not active."]],
                            ], 409));
                        }

                        if ($product->type === 'physical' && $item['quantity'] > $product->stock) {
                            abort(response()->json([
                                'status' => 'error',
                                'message' => 'Insufficient stock.',
                                'errors' => ['quantity' => ["Product '{$product->title}' only has {$product->stock} available."]],
                            ], 409));
                        }

                        $totalPrice = bcmul((string) $product->price, (string) $item['quantity'], 2);

                        $snapshots[] = [
                            'product_id' => $product->id,
                            'event_id' => null,
                            'item_type' => 'product',
                            'name' => $product->title,
                            'quantity' => $item['quantity'],
                            'unit_price' => $product->price,
                            'total_price' => $totalPrice,
                        ];

                        $subtotal = bcadd($subtotal, $totalPrice, 2);
                    }
                }

                // --- Buat order ---
                $order = $this->createOrder([
                    'user_id' => $user->id,
                    'status' => 'pending',
                    'customer_name' => $data['customer_name'] ?? $user->name,
                    'customer_email' => $data['customer_email'] ?? $user->email,
                    'subtotal_amount' => $subtotal,
                    'total_amount' => $subtotal, // MVP: total = subtotal (belum ada shipping/tax)
                    'currency' => 'IDR',
                    'notes' => $data['notes'] ?? null,
                    'expired_at' => now()->addHours((int) config('services.orders.expiry_hours', 24)),
                    'idempotency_key' => $idempotencyKey,
                ]);

                // --- Buat order items (snapshot harga) ---
                foreach ($snapshots as $snapshot) {
                    $this->orderItemRepository->create(array_merge(
                        $snapshot,
                        ['order_id' => $order->id]
                    ));
                }

                // --- Kosongkan cart (hanya jika source dari cart, bukan direct items) ---
                if (empty($data['items'])) {
                    $this->cartService->clear($cart);
                }

                $order->load(['items.product.image', 'items.event.image']);

                return $this->prepareOrder($order);
            });
        } catch (QueryException $exception) {
            if ($idempotencyKey && $exception instanceof UniqueConstraintViolationException) {
                $existingOrder = $this->orderRepository->findByIdempotencyKey($idempotencyKey, $user->id);
                if ($existingOrder) {
                    $existingOrder->load(['items.product.image', 'items.event.image']);

                    return $this->prepareOrder($existingOrder);
                }
            }

            throw $exception;
        }
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        $order = $this->orderRepository->findByOrderNumber($orderNumber);

        return $order ? $this->prepareOrder($order) : null;
    }

    public function findByIdForUser(string $orderId, User $user): ?Order
    {
        $order = $user->role === 'admin'
            ? $this->orderRepository->findById($orderId)
            : $this->orderRepository->findByIdAndUser($orderId, $user->id);

        if (! $order) {
            return null;
        }

        return $this->prepareOrder($order);
    }

    public function listForUser(User $user, array $filters = []): LengthAwarePaginator
    {
        $orders = $this->orderRepository->paginateByUser($user->id, $filters);
        $orders->getCollection()->transform(fn ($order) => $this->prepareOrder($order));

        return $orders;
    }

    public function listAll(array $filters = []): LengthAwarePaginator
    {
        $orders = $this->orderRepository->paginateAll($filters);
        $orders->getCollection()->transform(fn ($order) => $this->prepareOrder($order));

        return $orders;
    }

    public function cancel(Order $order): Order
    {
        $cancelled = DB::transaction(function () use ($order) {
            $locked = $this->orderRepository->findByIdForUpdate($order->id);
            abort_unless($locked, 404, 'Order not found.');
            abort_if($locked->status !== 'pending', 409, "Cannot cancel order with status '{$locked->status}'.");

            $pendingPayment = $this->paymentRepository->findPendingByOrder($locked->id);
            abort_if($pendingPayment?->gateway === 'midtrans', 409, 'Orders with a pending Midtrans payment cannot be cancelled locally.');

            $this->paymentRepository->cancelPendingByOrder($locked->id);

            return $this->orderRepository->update($locked, ['status' => 'cancelled']);
        }, 3);

        return $this->prepareOrder($cancelled);
    }

    public function adminUpdateStatus(Order $order, string $status): Order
    {
        if ($status === 'cancelled') {
            return $this->cancel($order);
        }

        $updated = DB::transaction(function () use ($order) {
            $locked = $this->orderRepository->findByIdForUpdate($order->id);
            abort_unless($locked, 404, 'Order not found.');
            abort_if($locked->status !== 'paid', 409, 'Only paid orders can be completed.');

            return $this->orderRepository->update($locked, ['status' => 'completed']);
        }, 3);

        return $this->prepareOrder($updated);
    }

    public function expirePending(): int
    {
        return $this->orderRepository->expirePending();
    }

    /** @param array<string, mixed> $data */
    private function createOrder(array $data): Order
    {
        for ($attempt = 0; $attempt < 3; $attempt++) {
            $orderNumber = $this->orderNumberService->generate();

            try {
                return $this->orderRepository->create(array_merge($data, ['order_number' => $orderNumber]));
            } catch (QueryException $exception) {
                $isOrderNumberCollision = $exception instanceof UniqueConstraintViolationException
                    && $this->orderRepository->findByOrderNumber($orderNumber);

                if (! $isOrderNumberCollision || $attempt === 2) {
                    throw $exception;
                }
            }
        }

        throw new \LogicException('Order number generation failed.');
    }
}
