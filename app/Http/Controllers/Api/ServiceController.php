<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceService $serviceService,
    ) {
        //
    }

    public function index(Request $request): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';

        $filters = $request->only(['search', 'category_id', 'is_active', 'is_featured', 'sort', 'direction', 'per_page', 'page']);

        $paginator = $this->serviceService->paginate($filters, $isAdmin);

        return response()->json([
            'status' => 'success',
            'message' => 'Services retrieved successfully.',
            'data' => ServiceResource::collection($paginator->items()),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
            'links' => [
                'first' => $paginator->url(1),
                'last' => $paginator->url($paginator->lastPage()),
                'prev' => $paginator->previousPageUrl(),
                'next' => $paginator->nextPageUrl(),
            ],
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $service = $this->serviceService->findBySlug($slug);

        if (! $service) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Service retrieved successfully.',
            'data' => new ServiceResource($service),
        ]);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $service = $this->serviceService->createService($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Service created successfully.',
            'data' => new ServiceResource($service),
        ], 201);
    }

    public function update(UpdateServiceRequest $request, Service $service): JsonResponse
    {
        $updatedService = $this->serviceService->updateService($service, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Service updated successfully.',
            'data' => new ServiceResource($updatedService),
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $this->serviceService->deleteService($service);

        return response()->json([
            'status' => 'success',
            'message' => 'Service deleted successfully.',
            'data' => null,
        ]);
    }
}
