<?php

namespace App\Repositories;

use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Order
    {
        return Order::query()->create($data);
    }

    public function findById(string $id): ?Order
    {
        return Order::query()->find($id);
    }

    public function findByIdAndUser(string $orderId, string $userId): ?Order
    {
        return Order::query()
            ->where('id', $orderId)
            ->where('user_id', $userId)
            ->first();
    }

    public function findByOrderNumber(string $orderNumber): ?Order
    {
        return Order::query()
            ->where('order_number', $orderNumber)
            ->first();
    }

    public function findByIdempotencyKey(string $key): ?Order
    {
        return Order::query()
            ->where('idempotency_key', $key)
            ->first();
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateByUser(string $userId, array $filters = []): LengthAwarePaginator
    {
        return Order::query()
            ->withCount('items')
            ->where('user_id', $userId)
            ->when($filters['status'] ?? null, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where('order_number', 'like', "%{$search}%")
            )
            ->when(
                $filters['sort'] ?? null,
                fn ($q, $sort) => $q->orderBy($sort, $filters['direction'] ?? 'desc'),
                fn ($q) => $q->latest()
            )
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateAll(array $filters = []): LengthAwarePaginator
    {
        return Order::query()
            ->withCount('items')
            ->when($filters['status'] ?? null, fn ($q, $status) =>
                $q->where('status', $status)
            )
            ->when($filters['search'] ?? null, fn ($q, $search) =>
                $q->where(function ($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('customer_email', 'like', "%{$search}%");
                })
            )
            ->when(
                $filters['sort'] ?? null,
                fn ($q, $sort) => $q->orderBy($sort, $filters['direction'] ?? 'desc'),
                fn ($q) => $q->latest()
            )
            ->paginate($filters['per_page'] ?? 15);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(Order $order, array $data): Order
    {
        Order::query()->where('id', $order->id)->update($data);

        return $order->fresh();
    }

    public function expirePending(): int
    {
        return Order::query()
            ->where('status', 'pending')
            ->where('expired_at', '<', now())
            ->update(['status' => 'expired']);
    }
}