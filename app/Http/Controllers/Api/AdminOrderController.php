<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
        //
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listAll($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Orders retrieved successfully.',
            'data' => OrderResource::collection($orders->items()),
            'meta' => [
                'current_page' => $orders->currentPage(),
                'last_page' => $orders->lastPage(),
                'per_page' => $orders->perPage(),
                'total' => $orders->total(),
            ],
        ]);
    }

    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $request->validate([
            'status' => ['required', 'string', 'in:completed,cancelled'],
        ]);

        $order = $this->orderService->adminUpdateStatus($order, $request->input('status'));
        $order->load(['items.product.image', 'items.event.image']);

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated.',
            'data' => new OrderResource($order),
        ]);
    }
}
