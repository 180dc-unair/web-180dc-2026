<?php

namespace App\Repositories\Contracts;

use App\Models\CartItem;

interface CartItemRepositoryInterface
{
    public function findByCartAndProduct(string $cartId, string $productId): ?CartItem;

    public function findByIdAndCart(string $itemId, string $cartId): ?CartItem;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): CartItem;

    /**
     * @param array<string, mixed> $data
     */
    public function update(CartItem $item, array $data): CartItem;

    public function delete(CartItem $item): void;
}
