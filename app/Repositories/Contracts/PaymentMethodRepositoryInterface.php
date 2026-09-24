<?php

namespace App\Repositories\Contracts;

use App\Models\PaymentMethod;
use Illuminate\Database\Eloquent\Collection;

interface PaymentMethodRepositoryInterface
{
    /**
     * @return Collection<int, PaymentMethod>
     */
    public function allActive(): Collection;

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, PaymentMethod>
     */
    public function all(array $filters = []): Collection;

    public function findByCode(string $code): ?PaymentMethod;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PaymentMethod;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PaymentMethod $method, array $data): PaymentMethod;

    public function hasPayments(PaymentMethod $method): bool;

    public function delete(PaymentMethod $method): void;
}
