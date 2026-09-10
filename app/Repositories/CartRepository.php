<?php

namespace App\Repositories;

use App\Models\Cart;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Database\QueryException;

class CartRepository implements CartRepositoryInterface
{

    public function firstOrCreateByUser(string $userId): Cart
    {
        try {
            return Cart::query()->firstOrCreate(['user_id' => $userId]);
        } catch (QueryException $e) {
            if ($e->getCode() === '23000') {
                return Cart::query()->where('user_id', $userId)->firstOrFail();
            }
            throw $e;
        }
    }
}
