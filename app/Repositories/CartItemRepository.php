<?php

namespace App\Repositories;

use App\Models\CartItem;
use App\Repositories\Contracts\CartItemRepositoryInterface;

class CartItemRepository implements CartItemRepositoryInterface
{
    public function findByCartAndProduct(string $cartId, string $productId): ?CartItem
    {
        return CartItem::query()
            ->where('cart_id', $cartId)
            ->where('product_id', $productId)
            ->lockForUpdate()
            ->first();
    }
    
    public function findByIdAndCart(string $itemId, string $cartId): ?CartItem
    {
        return CartItem::query()
            ->where('id', $itemId)
            ->where('cart_id', $cartId)
            ->first();
    }

    /**
     * @param array<string,mixed> $data
     */
    public function create(array $data): CartItem
    {
        return CartItem::query()->create($data);
    }

    /**
     * @param array<string,mixed> $data
     */
    public function update(CartItem $item, array $data): CartItem
    {
        $item->update($data);

        return $item->fresh();
    }

    public function delete(CartItem $item): void
    {
        $item->delete();
    }
}
