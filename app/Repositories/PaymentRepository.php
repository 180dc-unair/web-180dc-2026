<?php

namespace App\Repositories;

use App\Models\Payment;
use App\Repositories\Contracts\PaymentRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class PaymentRepository implements PaymentRepositoryInterface
{
    public function findById(string $id, bool $lock = false): ?Payment
    {
        return Payment::query()
            ->with(['method', 'proof'])
            ->when($lock, fn ($query) => $query->lockForUpdate())
            ->find($id);
    }

    public function findByIdAndUser(string $id, string $userId): ?Payment
    {
        return Payment::query()
            ->with(['method', 'proof'])
            ->where('id', $id)
            ->whereHas('order', fn ($q) => $q->where('user_id', $userId))
            ->first();
    }

    public function findByReference(string $reference, ?string $paymentId = null): ?Payment
    {
        return Payment::query()
            ->where('gateway_reference', $reference)
            ->when($paymentId, fn ($query) => $query->orWhere('id', $paymentId))
            ->first();
    }

    public function findByIdempotencyKey(string $orderId, string $key): ?Payment
    {
        return Payment::query()
            ->where('order_id', $orderId)
            ->where('idempotency_key', $key)
            ->first();
    }

    /**
     * @return Collection<int, Payment>
     */
    public function allByOrder(string $orderId): Collection
    {
        return Payment::query()
            ->where('order_id', $orderId)
            ->with(['method', 'proof'])
            ->latest()
            ->get();
    }

    public function findPendingByOrder(string $orderId): ?Payment
    {
        return Payment::query()
            ->where('order_id', $orderId)
            ->where('status', 'pending')
            ->where('expired_at', '>', now())
            ->first();
    }

    public function cancelPendingByOrder(string $orderId): int
    {
        return Payment::query()
            ->where('order_id', $orderId)
            ->where('status', 'pending')
            ->update(['status' => 'cancelled']);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Payment
    {
        return Payment::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Payment $payment, array $data): Payment
    {
        $payment->update($data);

        return $payment->fresh();
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginateAll(array $filters = []): LengthAwarePaginator
    {
        $query = Payment::query()
            ->with(['method', 'order', 'proof'])
            ->latest();

        if (! empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (! empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }

        if (! empty($filters['order_number'])) {
            $query->whereHas('order', fn ($q) => $q->where('order_number', 'like', '%'.$filters['order_number'].'%'));
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function expirePending(): int
    {
        return Payment::query()
            ->where('status', 'pending')
            ->where('expired_at', '<', now())
            ->update(['status' => 'expired']);
    }
}
