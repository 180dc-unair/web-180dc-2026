<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PaymentMethodResource;
use App\Services\PaymentMethodService;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function __construct(
        private readonly PaymentMethodService $paymentMethodService,
    ) {
        //
    }

    public function index(): JsonResponse
    {
        $methods = $this->paymentMethodService->allActive();

        return response()->json([
            'status' => 'success',
            'message' => 'Payment methods retrieved successfully.',
            'data' => PaymentMethodResource::collection($methods),
        ]);
    }
}
