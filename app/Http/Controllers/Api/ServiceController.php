<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Service\StoreServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Services\ServiceService;
use Illuminate\Http\JsonResponse;

class ServiceController extends Controller
{
    public function __construct(
        private readonly ServiceService $serviceService,
    ) {
        //
    }

    public function index(): JsonResponse
    {
        $services = $this->serviceService->getServices();

        return response()->json([
            'status' => 'success',
            'message' => 'Services retrieved successfully.',
            'data' => ServiceResource::collection($services),
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
