<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\AdminDashboardService;
use Illuminate\Http\JsonResponse;

class AdminDashboardController extends Controller
{
    public function __construct(
        private readonly AdminDashboardService $dashboardService,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Admin dashboard summary retrieved successfully.',
            'data' => $this->dashboardService->summary(),
        ]);
    }
}
