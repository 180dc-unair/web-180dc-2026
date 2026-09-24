<?php

namespace App\Repositories\Contracts;

use App\Models\OrderItem;

interface OrderItemRepositoryInterface
{
    public function create(array $data): OrderItem;
}