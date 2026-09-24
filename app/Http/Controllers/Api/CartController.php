<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CartResource;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function __construct(
        private readonly CartService $cartService
    ) {
        //
    }

    public function show(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCartWithSummary($request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Cart retrieved successfully.',
            'data' => new CartResource($cart),
        ]);
    }

    public function clear(Request $request): JsonResponse
    {
        $cart = $this->cartService->getCurrentCart($request->user());
        $this->cartService->clear($cart);

        $cart = $this->cartService->getCartWithSummary($request->user());
        
        return response()->json([
            'status' => 'success',
            'message' => 'Cart cleared successfully.',
            'data' => new CartResource($cart),
        ]);
    }
}
