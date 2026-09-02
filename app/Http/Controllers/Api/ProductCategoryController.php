<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProductCategory\StoreProductCategoryRequest;
use App\Http\Requests\ProductCategory\UpdateProductCategoryRequest;
use App\Http\Resources\ProductCategoryResource;
use App\Models\ProductCategory;
use App\Services\ProductCategoryService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductCategoryController extends Controller
{
    public function __construct(
        private readonly ProductCategoryService $productCategoryService,
    ) {
        //
    }

    public function index(Request $request): JsonResponse
    {
        $search = $request->query('search');

        $productCategories = $this->productCategoryService->getProductCategories($search);

        return response()->json([
            'status' => 'success',
            'message' => 'Categories retrieved successfully.',
            'data' => ProductCategoryResource::collection($productCategories),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $productCategory = $this->productCategoryService->getProductCategoryBySlug($slug);

        if (! $productCategory) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product category not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product category retrieved successfully.',
            'data' => new ProductCategoryResource($productCategory),
        ]);
    }

    public function store(StoreProductCategoryRequest $request): JsonResponse
    {
        $productCategory = $this->productCategoryService->createProductCategory($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product category created successfully.',
            'data' => new ProductCategoryResource($productCategory),
        ], 201);
    }

    public function update(UpdateProductCategoryRequest $request, ProductCategory $productCategory): JsonResponse
    {
        $updatedProductCategory = $this->productCategoryService->updateProductCategory($productCategory, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product category updated successfully.',
            'data' => new ProductCategoryResource($updatedProductCategory),
        ]);
    }

    public function destroy(ProductCategory $productCategory): JsonResponse
    {
        $this->productCategoryService->deleteProductCategory($productCategory);

        return response()->json([
            'status' => 'success',
            'message' => 'Product category deleted successfully.',
            'data' => null,
        ]);
    }
}
