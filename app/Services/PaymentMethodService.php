<?php

namespace App\Services;

use App\Models\PaymentMethod;
use App\Repositories\Contracts\PaymentMethodRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class PaymentMethodService
{
    public function __construct(
        private readonly PaymentMethodRepositoryInterface $paymentMethodRepository,
    ) {
        //
    }

    /**
     * @return Collection<int, PaymentMethod>
     */
    public function allActive(): Collection
    {
        return $this->paymentMethodRepository->allActive();
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, PaymentMethod>
     */
    public function all(array $filters = []): Collection
    {
        return $this->paymentMethodRepository->all($filters);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PaymentMethod
    {
        return $this->paymentMethodRepository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PaymentMethod $paymentMethod, array $data): PaymentMethod
    {
        if ($this->paymentMethodRepository->hasPayments($paymentMethod) && (isset($data['gateway']) || isset($data['code']))) {
            abort(409, 'Used payment method code and gateway cannot be changed.');
        }

        return $this->paymentMethodRepository->update($paymentMethod, $data);
    }

    public function delete(PaymentMethod $paymentMethod): void
    {
        if ($this->paymentMethodRepository->hasPayments($paymentMethod)) {
            abort(409, 'Cannot delete payment method that has been used by payments.');
        }

        $this->paymentMethodRepository->delete($paymentMethod);
    }
}
