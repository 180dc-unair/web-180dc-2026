<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Product\StoreProductRequest;
use App\Http\Requests\Product\ToggleProductRequest;
use App\Http\Requests\Product\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use App\Services\ProductService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly ProductService $productService,
    ) {
        //
    }

    public function index(Request $request): JsonResponse
    {
        $filters = $request->only([
            'search', 'category_id', 'type', 'status', 'is_featured', 'is_best_seller', 'sort_by', 'sort_direction',
        ]);

        $isAdmin = $request->user()?->role === 'admin';

        $products = $this->productService->getProducts($filters, $isAdmin);

        return response()->json([
            'status' => 'success',
            'message' => 'Products retrieved successfully.',
            'data' => ProductResource::collection($products),
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';

        $product = $this->productService->getProductBySlug($slug, $isAdmin);

        if (!$product) {
            return response()->json([
                'status' => 'error',
                'message' => 'Product not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Product retrieved successfully.',
            'data' => new ProductResource($product),
        ]);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = $this->productService->createProduct($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product created successfully.',
            'data' => new ProductResource($product),
        ], 201);
    }

    public function update(UpdateProductRequest $request, Product $product): JsonResponse
    {
        $updatedProduct = $this->productService->updateProduct($product, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Product updated successfully.',
            'data' => new ProductResource($updatedProduct),
        ]);
    }

    public function toggle(ToggleProductRequest $request, Product $product): JsonResponse
    {
        $updatedProduct = $this->productService->toggleProductBoolean($product, $request->validated('field'));

        return response()->json([
            'status' => 'success',
            'message' => 'Product toggled successfully.',
            'data' => new ProductResource($updatedProduct),
        ]);
    }

    public function destroy(Product $product): JsonResponse
    {
        $this->productService->deleteProduct($product);

        return response()->json([
            'status' => 'success',
            'message' => 'Product deleted successfully.',
            'data' => null,
        ]);
    }
}
