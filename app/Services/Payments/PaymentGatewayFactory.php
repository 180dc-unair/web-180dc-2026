<?php

namespace App\Services\Payments;

use App\Services\Payments\Contracts\PaymentGatewayInterface;
use App\Services\Payments\Gateways\ManualGateway;
use App\Services\Payments\Gateways\MidtransGateway;
use InvalidArgumentException;

class PaymentGatewayFactory
{
    public function make(string $gateway): PaymentGatewayInterface
    {
        return match ($gateway) {
            'manual' => app(ManualGateway::class),
            'midtrans' => app(MidtransGateway::class),
            default => throw new InvalidArgumentException("Unknown gateway $gateway"),
        };
    }
}
