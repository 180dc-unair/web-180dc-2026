<?php

namespace App\Repositories;

use App\Models\PaymentWebhook;
use App\Repositories\Contracts\PaymentWebhookRepositoryInterface;

class PaymentWebhookRepository implements PaymentWebhookRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PaymentWebhook
    {
        return PaymentWebhook::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PaymentWebhook $webhook, array $data): PaymentWebhook
    {
        $webhook->update($data);

        return $webhook->fresh();
    }
}
