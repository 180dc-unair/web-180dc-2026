<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\User;
use App\Repositories\Contracts\CartRepositoryInterface;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function __construct(
        private readonly CartRepositoryInterface $cartRepository,
    ) {
        //
    }

    public function getCurrentCart(User $user): Cart
    {
        return DB::transaction(function () use ($user) {
            return $this->cartRepository->firstOrCreateByUser($user->id);
        });
    }

    public function clear(Cart $cart): void
    {
        DB::transaction(function () use ($cart) {
            $cart->items()->lockForUpdate()->get();
            $cart->items()->delete();
            $cart->touch();
        });
    }

    public function getCartWithSummary(User $user): Cart
    {
        $cart = $this->getCurrentCart($user);
        $cart->load(['items.product.image']);

        foreach ($cart->items as $item) {
            $product = $item->product;

            $item->line_total_amount = $product
                ? bcmul($product->price, (string) $item->quantity, 2)
                : '0.00';

            if (! $product || $product->status !== 'active') {
                $item->is_valid = false;
                $item->invalid_reason = 'inactive';
            } elseif ($product->type === 'physical' && $item->quantity > $product->stock) {
                $item->is_valid = false;
                $item->invalid_reason = 'out_of_stock';
            } else {
                $item->is_valid = true;
                $item->invalid_reason = null;
            }
        }

        $validItems = $cart->items->filter(fn ($item) => $item->is_valid);

        $cart->summary = [
            'item_count' => $cart->items->count(),
            'total_quantity' => $validItems->sum('quantity'),
            'subtotal_amount' => $validItems->reduce(
                fn ($carry, $item) => bcadd($carry, $item->line_total_amount, 2), '0.00'
            ),
            'currency' => 'IDR',
            'invalid_items_count' => $cart->items->count() - $validItems->count(),
        ];
        
        return $cart;
    }
}
