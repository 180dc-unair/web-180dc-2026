<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartItem\AddCartItemRequest;
use App\Http\Requests\CartItem\UpdateCartItemRequest;
use App\Http\Resources\CartItemResource;
use App\Models\CartItem;
use App\Services\CartItemService;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartItemController extends Controller
{
    public function __construct(
        private readonly CartItemService $cartItemService,
        private readonly CartService $cartService,
    ) {
        //
    }

    public function store(AddCartItemRequest $request): JsonResponse
    {
        $item = $this->cartItemService->add(
            $request->user(),
            $request->validated('product_id'),
            $request->validated('quantity'),
        );

        $cart = $this->cartService->getCartWithSummary($request->user());
        $preparedItem = $cart->items->firstWhere('id', $item->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart item added successfully.',
            'data' => new CartItemResource($preparedItem),
            'meta' => [
                'cart_summary' => $cart->summary,
            ],
        ], 201);
    }

    public function update(UpdateCartItemRequest $request, CartItem $cartItem): JsonResponse
    {
        $item = $this->cartItemService->updateQuantity(
            $request->user(),
            $cartItem,
            $request->validated('quantity'),
        );

        $cart = $this->cartService->getCartWithSummary($request->user());
        $preparedItem = $cart->items->firstWhere('id', $item->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Cart item updated.',
            'data' => new CartItemResource($preparedItem),
            'meta' => [
                'cart_summary' => $cart->summary,
            ],
        ]);        
    }

    public function destroy(Request $request, CartItem $cartItem): JsonResponse
    {
        $this->cartItemService->remove($request->user(), $cartItem);

        $cart = $this->cartService->getCartWithSummary($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Cart item removed.',
            'data' => null,
            'meta' => [
                'cart_summary' => $cart->summary,
            ],
        ]);
    }
}
