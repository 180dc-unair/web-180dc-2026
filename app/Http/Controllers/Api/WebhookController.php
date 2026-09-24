<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\WebhookService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WebhookController extends Controller
{
    public function __construct(
        private readonly WebhookService $webhookService,
    ) {
        //
    }

    public function handle(string $gateway, Request $request): JsonResponse
    {
        $result = $this->webhookService->handle($gateway, $request->headers->all(), $request->getContent());

        return response()->json([
            'status' => $result['status'],
            'message' => 'Webhook processed successfully.',
            'data' => null,
        ]);
    }
}
