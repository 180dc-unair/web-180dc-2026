<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Article\StoreArticleRequest;
use App\Http\Requests\Article\UpdateArticleRequest;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use App\Services\ArticleService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    public function __construct(
        private readonly ArticleService $articleService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';

        return response()->json([
            'status' => 'success',
            'message' => 'Articles retrieved successfully.',
            'data' => ArticleResource::collection(
                $this->articleService->getArticles(
                    $request->query('search'),
                    $request->query('category'),
                    $isAdmin,
                ),
            ),
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';
        $article = $this->articleService->getBySlug($slug, $isAdmin, ! $isAdmin);

        if (! $article) {
            return response()->json([
                'status' => 'error',
                'message' => 'Article not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Article retrieved successfully.',
            'data' => new ArticleResource($article),
        ]);
    }

    public function store(StoreArticleRequest $request): JsonResponse
    {
        $article = $this->articleService->create($request->validated(), $request->user()->id);

        return response()->json([
            'status' => 'success',
            'message' => 'Article created successfully.',
            'data' => new ArticleResource($article),
        ], 201);
    }

    public function update(UpdateArticleRequest $request, Article $article): JsonResponse
    {
        $article = $this->articleService->update($article, $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Article updated successfully.',
            'data' => new ArticleResource($article),
        ]);
    }

    public function destroy(Article $article): JsonResponse
    {
        $this->articleService->delete($article);

        return response()->json([
            'status' => 'success',
            'message' => 'Article deleted successfully.',
            'data' => null,
        ]);
    }
}
