<?php

namespace App\Services;

use App\Models\CartItem;
use App\Models\Product;
use App\Models\User;
use App\Repositories\Contracts\CartItemRepositoryInterface;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CartItemService
{
    public function __construct(
        private readonly CartItemRepositoryInterface $cartItemRepository,
        private readonly CartService $cartService,
    ) {
        //
    }

    public function add(User $user, string $productId, int $quantity): CartItem
    {
        return DB::transaction(function () use ($user, $productId, $quantity) {
            $cart = $this->cartService->getCurrentCart($user);

            $product =  Product::query()->lockForUpdate()->findOrFail($productId);

            if ($product->status !== 'active') {
                abort(response()->json([
                    'status' => 'error',
                    'message' => 'Product not available.',
                    'errors' => ['product_id' => ['Product is not active.']],
                ], 409));
            }

            if ($product->type === 'physical' && $product->stock === 0) {
                abort(response()->json([
                    'status' => 'error',
                    'message' => 'Product out of stock.',
                    'errors' => ['product_id' => ['Product is out of stock.']],
                ], 409));
            }

            $existingItem = $this->cartItemRepository->findByCartAndProduct($cart->id, $productId);
            $newQty = $existingItem ? $existingItem->quantity + $quantity : $quantity;

            if ($product->type === 'physical' && $newQty > $product->stock) {
                abort(response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient stock.',
                    'errors' => ['quantity' => ["Only {$product->stock} available."]],
                ], 409));
            }

            if ($newQty > 99) {
                throw ValidationException::withMessages([
                    'quantity' => ['Maximum 99 per item.'],
                ]);
            }

            if ($existingItem) {
                $item = $this->cartItemRepository->update($existingItem, ['quantity' => $newQty]);
            } else {
                try {
                    $item = $this->cartItemRepository->create([
                        'cart_id' => $cart->id,
                        'product_id' => $productId,
                        'quantity' => $quantity,
                    ]);
                } catch (QueryException $exception) {
                    if ($exception->errorInfo[1] === 1062) {
                        $existingItem = $this->cartItemRepository->findByCartAndProduct($cart->id, $productId);
                        abort_unless($existingItem, 500, 'Cart item race condition could not be resolved.');
                        $mergedQty = min($existingItem->quantity + $quantity, 99);
                        $item = $this->cartItemRepository->update($existingItem, ['quantity' => $mergedQty]);
                    } else {
                        throw $exception;
                    }
                }
            }

            $cart->touch();

            return $item->load('product.image');
        });
    }

    public function updateQuantity(User $user, CartItem $cartItem, int $quantity): CartItem
    {
        return DB::transaction(function () use ($user, $cartItem, $quantity) {
            $cart = $this->cartService->getCurrentCart($user);

            $item = $this->cartItemRepository->findByIdAndCart($cartItem->id, $cart->id);

            if (! $item) {
                abort(response()->json([
                    'status' => 'error',
                    'message' => 'Cart item not found.',
                    'data' => null,
                ], 404));
            }

            $product = Product::query()->lockForUpdate()->findOrFail($item->product_id);

            if ($product->status !== 'active') {
                abort(response()->json([
                    'status' => 'error',
                    'message' => 'Product not available.',
                    'errors' => ['product_id' => ['Product is no longer active.']],
                ], 409));
            }

            if ($product->type == 'physical' && $quantity > $product->stock) {
                abort(response()->json([
                    'status' => 'error',
                    'message' => 'Insufficient stock.',
                    'errors' => ['quantity' => ["Only {$product->stock} available."]],
                ], 409));
            }

            $item = $this->cartItemRepository->update($item, ['quantity' => $quantity]);
            $cart->touch();

            return $item->load('product.image');
        });
    }

    public function remove(User $user, CartItem $cartItem): void
    {
        DB::transaction(function () use ($user, $cartItem) {
            $cart = $this->cartService->getCurrentCart($user);

            $item = $this->cartItemRepository->findByIdAndCart($cartItem->id, $cart->id);

            if (! $item) {
                abort(response()->json([
                    'status' => 'error',
                    'message' => 'Cart item not found.',
                    'data' => null,
                ], 404));
            }

            $this->cartItemRepository->delete($item);
            $cart->touch();
        });
    }
}