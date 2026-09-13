<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface OrderRepositoryInterface
{
    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): Order;

    public function findById(string $id): ?Order;

    public function findByIdAndUser(string $orderId, string $userId): ?Order;

    public function findByOrderNumber(string $orderNumber): ?Order;

    public function findByIdempotencyKey(string $key): ?Order;

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateByUser(string $userId, array $filters = []): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $filters
     */
    public function paginateAll(array $filters = []): LengthAwarePaginator;

    /**
     * @param array<string, mixed> $data
     */
    public function update(Order $order, array $data): Order;

    public function expirePending(): int;
}