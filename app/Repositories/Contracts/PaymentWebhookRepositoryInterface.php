<?php

namespace App\Repositories\Contracts;

use App\Models\PaymentWebhook;

interface PaymentWebhookRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): PaymentWebhook;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(PaymentWebhook $webhook, array $data): PaymentWebhook;
}
