<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleCategory\StoreArticleCategoryRequest;
use App\Http\Requests\ArticleCategory\UpdateArticleCategoryRequest;
use App\Http\Resources\ArticleCategoryResource;
use App\Http\Resources\ArticleResource;
use App\Models\ArticleCategory;
use App\Services\ArticleCategoryService;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ArticleCategoryController extends Controller
{
    public function __construct(
        private readonly ArticleCategoryService $categoryService,
        private readonly ArticleService $articleService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Article categories retrieved successfully.',
            'data' => ArticleCategoryResource::collection(
                $this->categoryService->getCategories($request->query('search')),
            ),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $category = $this->categoryService->getBySlug($slug);

        if (! $category) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article category not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Article category retrieved successfully.',
            'data' => new ArticleCategoryResource($category),
        ]);
    }

    public function articles(Request $request, string $slug): JsonResponse
    {
        if (! $this->categoryService->getBySlug($slug)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article category not found.',
                'data' => null,
            ], 404);
        }

        $isAdmin = Auth::guard('sanctum')->user()?->role === 'admin';

        return response()->json([
            'status' => 'success',
            'message' => 'Articles retrieved by category successfully.',
            'data' => ArticleResource::collection(
                $this->articleService->getArticles(
                    $request->query('search'),
                    $slug,
                    $isAdmin,
                ),
            ),
        ]);
    }

    public function store(StoreArticleCategoryRequest $request): JsonResponse
    {
        $category = $this->categoryService->create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Article category created successfully.',
            'data' => new ArticleCategoryResource($category),
        ], 201);
    }

    public function update(UpdateArticleCategoryRequest $request, ArticleCategory $articleCategory): JsonResponse
    {
        $category = $this->categoryService->update($articleCategory, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Article category updated successfully.',
            'data' => new ArticleCategoryResource($category),
        ]);
    }

    public function destroy(ArticleCategory $articleCategory): JsonResponse
    {
        $this->categoryService->delete($articleCategory);

        return response()->json([
            'status' => 'success',
            'message' => 'Article category deleted successfully.',
            'data' => null,
        ]);
    }
}
