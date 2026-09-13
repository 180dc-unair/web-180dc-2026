<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Str;

class OrderNumberService
{
    public function generate(): string
    {
        $date = now()->format('Ymd');

        for ($i = 0; $i < 3; $i++) {
            $random = strtoupper(Str::random(6));
            $orderNumber = "ORD-{$date}-{$random}";

            if (! Order::query()->where('order_number', $orderNumber)->exists()) {
                return $orderNumber;
            }
        }

        return "ORD-{$date}-" . strtoupper(Str::random(8));
    }
}