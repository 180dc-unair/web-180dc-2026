<?php

namespace App\Repositories\Contracts;

use App\Models\Payment;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface PaymentRepositoryInterface
{
    public function findById(string $id, bool $lock = false): ?Payment;

    public function findByIdAndUser(string $id, string $userId): ?Payment;

    public function findByReference(string $reference, ?string $paymentId = null): ?Payment;

    public function findByIdempotencyKey(string $orderId, string $key): ?Payment;

    /**
     * @return Collection<int, Payment>
     */
    public function allByOrder(string $orderId): Collection;

    public function findPendingByOrder(string $orderId): ?Payment;

    public function cancelPendingByOrder(string $orderId): int;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Payment;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Payment $payment, array $data): Payment;

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateAll(array $filters = []): LengthAwarePaginator;

    public function expirePending(): int;
}
