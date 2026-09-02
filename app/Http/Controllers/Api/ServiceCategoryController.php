<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ServiceCategory\StoreServiceCategoryRequest;
use App\Http\Requests\ServiceCategory\UpdateServiceCategoryRequest;
use App\Http\Resources\ServiceCategoryResource;
use App\Http\Resources\ServiceResource;
use App\Models\ServiceCategory;
use App\Services\ServiceCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ServiceCategoryController extends Controller
{
    public function __construct(
        private readonly ServiceCategoryService $serviceCategoryService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Service categories retrieved successfully.',
            'data' => ServiceCategoryResource::collection(
                $this->serviceCategoryService->getCategories($request->query('search')),
            ),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $category = $this->serviceCategoryService->getBySlug($slug);

        if (! $category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service category not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Service category retrieved successfully.',
            'data' => new ServiceCategoryResource($category),
        ]);
    }

    public function services(string $slug): JsonResponse
    {
        $category = $this->serviceCategoryService->getBySlugWithServices($slug);

        if (! $category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Service category not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Services retrieved by category successfully.',
            'data' => ServiceResource::collection($category->services),
        ]);
    }

    public function store(StoreServiceCategoryRequest $request): JsonResponse
    {
        $category = $this->serviceCategoryService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Service category created successfully.',
            'data' => new ServiceCategoryResource($category),
        ], 201);
    }

    public function update(UpdateServiceCategoryRequest $request, ServiceCategory $serviceCategory): JsonResponse
    {
        $category = $this->serviceCategoryService->update($serviceCategory, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Service category updated successfully.',
            'data' => new ServiceCategoryResource($category),
        ]);
    }

    public function destroy(ServiceCategory $serviceCategory): JsonResponse
    {
        $this->serviceCategoryService->delete($serviceCategory);

        return response()->json([
            'status' => 'success',
            'message' => 'Service category deleted successfully.',
            'data' => null,
        ]);
    }
}
