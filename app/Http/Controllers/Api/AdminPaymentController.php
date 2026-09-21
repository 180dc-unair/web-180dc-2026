<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\ListPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Payment;
use App\Services\PaymentService;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminPaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly WebhookService $webhookService,
    ) {
        //
    }

    /**
     * GET /api/admin/payments — List all payments.
     */
    public function index(ListPaymentRequest $request): JsonResponse
    {
        $payments = $this->paymentService->listAll($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Payments retrieved successfully.',
            'data' => PaymentResource::collection($payments->items()),
            'meta' => [
                'current_page' => $payments->currentPage(),
                'last_page' => $payments->lastPage(),
                'per_page' => $payments->perPage(),
                'total' => $payments->total(),
            ],
        ]);
    }

    /**
     * POST /api/admin/payments/{payment}/confirm — Admin confirm manual payment.
     * Marks payment as paid, order as paid, and decrements physical stock.
     */
    public function confirm(Request $request, Payment $payment): JsonResponse
    {
        $confirmed = $this->webhookService->confirmManual($request->user(), $payment);

        return response()->json([
            'status' => 'success',
            'message' => 'Payment confirmed successfully.',
            'data' => new PaymentResource($confirmed),
        ]);
    }
}
