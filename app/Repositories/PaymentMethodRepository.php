<?php

namespace App\Repositories;

use App\Models\PaymentMethod;
use App\Repositories\Contracts\PaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PaymentMethodRepository implements PaymentMethodRepositoryInterface
{
    /**
     * @return Collection<int, PaymentMethod>
     */
    public function allActive(): Collection
    {
        return PaymentMethod::query()
            ->where('is_active', true)
            ->whereIn('gateway', ['manual', 'midtrans'])
            ->orderBy('name')
            ->get();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, PaymentMethod>
     */
    public function all(array $filters = []): Collection
    {
        $query = PaymentMethod::query()->orderBy('name');

        if (isset($filters['is_active'])) {
            $query->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN));
        }

        if (! empty($filters['gateway'])) {
            $query->where('gateway', $filters['gateway']);
        }

        return $query->get();
    }

    public function findByCode(string $code): ?PaymentMethod
    {
        return PaymentMethod::query()->where('code', $code)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PaymentMethod
    {
        return PaymentMethod::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PaymentMethod $method, array $data): PaymentMethod
    {
        $method->update($data);

        return $method->fresh();
    }

    public function hasPayments(PaymentMethod $method): bool
    {
        return $method->payments()->exists();
    }

    public function delete(PaymentMethod $method): void
    {
        $method->delete();
    }
}
