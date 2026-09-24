<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\PaymentMethod\ListPaymentMethodRequest;
use App\Http\Requests\PaymentMethod\StorePaymentMethodRequest;
use App\Http\Requests\PaymentMethod\UpdatePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\PaymentMethod;
use App\Services\PaymentMethodService;
use Illuminate\Http\JsonResponse;

class AdminPaymentMethodController extends Controller
{
    public function __construct(
        private readonly PaymentMethodService $paymentMethodService,
    ) {
        //
    }

    public function index(ListPaymentMethodRequest $request): JsonResponse
    {
        $methods = $this->paymentMethodService->all($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Payment methods retrieved successfully.',
            'data' => PaymentMethodResource::collection($methods),
        ]);
    }

    public function store(StorePaymentMethodRequest $request): JsonResponse
    {
        $method = $this->paymentMethodService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Payment method created successfully.',
            'data' => new PaymentMethodResource($method),
        ], 201);
    }

    public function update(UpdatePaymentMethodRequest $request, PaymentMethod $paymentMethod): JsonResponse
    {
        $updated = $this->paymentMethodService->update($paymentMethod, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Payment method updated successfully.',
            'data' => new PaymentMethodResource($updated),
        ]);
    }

    public function destroy(PaymentMethod $paymentMethod): JsonResponse
    {
        $this->paymentMethodService->delete($paymentMethod);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment method deleted successfully.',
            'data' => null,
        ]);
    }
}
