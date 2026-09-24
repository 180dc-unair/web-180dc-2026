<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Payment\StorePaymentRequest;
use App\Http\Requests\Payment\UploadPaymentProofRequest;
use App\Http\Resources\PaymentResource;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService,
    ) {
        //
    }

    /**
     * POST /api/orders/{order}/payments — Create payment attempt.
     */
    public function store(StorePaymentRequest $request, Order $order): JsonResponse
    {
        // Cek ownership: user harus pemilik order (atau admin)
        $user = $request->user();
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
                'data' => null,
            ], 404);
        }

        $payment = $this->paymentService->create($order, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Payment created.',
            'data' => new PaymentResource($payment),
        ], 201);
    }

    /**
     * GET /api/payments/{payment} — Poll payment status.
     */
    public function show(Request $request, Payment $payment): JsonResponse
    {
        $found = $this->paymentService->findByIdForUser($payment->id, $request->user());

        if (! $found) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found.',
                'data' => null,
            ], 404);
        }

        $found->load('method');

        return response()->json([
            'status' => 'success',
            'message' => 'Payment retrieved successfully.',
            'data' => new PaymentResource($found),
        ]);
    }

    /**
     * GET /api/orders/{order}/payments — List payments for order.
     */
    public function index(Request $request, Order $order): JsonResponse
    {
        $user = $request->user();
        if ($user->role !== 'admin' && $order->user_id !== $user->id) {
            return response()->json([
                'status' => 'error',
                'message' => 'Order not found.',
                'data' => null,
            ], 404);
        }

        $payments = $this->paymentService->allByOrder($order->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Payments retrieved successfully.',
            'data' => PaymentResource::collection($payments),
        ]);
    }

    /**
     * POST /api/payments/{payment}/cancel — Cancel pending payment.
     */
    public function cancel(Request $request, Payment $payment): JsonResponse
    {
        $found = $this->paymentService->findByIdForUser($payment->id, $request->user());

        if (! $found) {
            return response()->json([
                'status' => 'error',
                'message' => 'Payment not found.',
                'data' => null,
            ], 404);
        }

        $cancelled = $this->paymentService->cancel($found);
        $cancelled->load('method');

        return response()->json([
            'status' => 'success',
            'message' => 'Payment cancelled successfully.',
            'data' => new PaymentResource($cancelled),
        ]);
    }

    public function uploadProof(UploadPaymentProofRequest $request, Payment $payment): JsonResponse
    {
        $found = $this->paymentService->findByIdForUser($payment->id, $request->user());
        abort_unless($found, 404, 'Payment not found.');

        $updated = $this->paymentService->uploadProof($found, $request->file('proof'));

        return response()->json([
            'status' => 'success',
            'message' => 'Payment proof uploaded successfully.',
            'data' => new PaymentResource($updated),
        ], 201);
    }
}
