<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\ListAdminOrderRequest;
use App\Http\Requests\Order\UpdateOrderStatusRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;

class AdminOrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
    }

    public function index(ListAdminOrderRequest $request): JsonResponse
    {
        $orders = $this->orderService->listAll($request->validated());

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

    public function show(Order $order): JsonResponse
    {
        $order->load(['items.product.image', 'items.event.image', 'payments.method', 'payments.proof']);
        $order = $this->orderService->prepareOrder($order);

        return response()->json([
            'status' => 'success',
            'message' => 'Order retrieved successfully.',
            'data' => new OrderResource($order),
        ]);
    }

    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): JsonResponse
    {
        $order = $this->orderService->adminUpdateStatus($order, $request->validated('status'));
        $order->load(['items.product.image', 'items.event.image']);

        return response()->json([
            'status' => 'success',
            'message' => 'Order status updated.',
            'data' => new OrderResource($order),
        ]);
    }
}
