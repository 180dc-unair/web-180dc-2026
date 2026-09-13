<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(
        private readonly OrderService $orderService,
    ) {
        //
    }

    public function index(Request $request): JsonResponse
    {
        $orders = $this->orderService->listForUser(
            $request->user(),
            $request->all()
        );

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

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $data = $request->validated();

        $idempotencyKey = $request->header('Idempotency-Key');
        if ($idempotencyKey) {
            $data['idempotency_key'] = $idempotencyKey;
        }

        $order = $this->orderService->checkout($request->user(), $data);

        return response()->json([
            'status' => 'success',
            'message' => 'Order created successfully.',
            'data' => new OrderResource($order),
        ], 201);
    }

    public function show(Request $request, Order $order): JsonResponse
    {
        $found = $this->orderService->findByIdForUser($order->id, $request->user());

        if (! $found) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
                'data' => null,
            ], 404);
        }

        $found->load(['items.product.image', 'items.event.image']);

        return response()->json([
            'status' => 'success',
            'message' => 'Order retrieved successfully.',
            'data' => new OrderResource($found),
        ]);
    }

    public function cancel(Request $request, Order $order): JsonResponse
    {
        $found = $this->orderService->findByIdForUser($order->id, $request->user());

        if (! $found) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
                'data' => null,
            ], 404);
        }

        $cancelled = $this->orderService->cancel($found);
        $cancelled->load(['items.product.image', 'items.event.image']);

        return response()->json([
            'status' => 'success',
            'message' => 'Order cancelled successfully.',
            'data' => new OrderResource($cancelled),
        ]);
    }
}
