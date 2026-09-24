<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\ArticleComment\ModerateArticleCommentRequest;
use App\Http\Requests\ArticleComment\StoreArticleCommentRequest;
use App\Http\Requests\ArticleComment\UpdateArticleCommentRequest;
use App\Http\Resources\ArticleCommentResource;
use App\Models\Article;
use App\Models\ArticleComment;
use App\Services\ArticleCommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleCommentController extends Controller
{
    public function __construct(
        private readonly ArticleCommentService $commentService,
    ) {}

    public function index(Request $request, Article $article): JsonResponse
    {
        $isAdmin = $request->user()?->role === 'admin';

        if (! $isAdmin && $article->status !== 'published') {
            return response()->json([
                'status' => 'error',
                'message' => 'Article not found.',
                'data' => null,
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Article comments retrieved successfully.',
            'data' => ArticleCommentResource::collection(
                $this->commentService->getComments($article, $isAdmin),
            ),
        ]);
    }

    public function store(StoreArticleCommentRequest $request, Article $article): JsonResponse
    {
        abort_unless($article->status === 'published', 404, 'Article not found.');

        $comment = $this->commentService->create($article, $request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Article comment created successfully.',
            'data' => new ArticleCommentResource($comment),
        ], 201);
    }

    public function update(UpdateArticleCommentRequest $request, ArticleComment $articleComment): JsonResponse
    {
        $comment = $this->commentService->update($articleComment, $request->user(), $request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Article comment updated successfully.',
            'data' => new ArticleCommentResource($comment),
        ]);
    }

    public function destroy(Request $request, ArticleComment $articleComment): JsonResponse
    {
        $this->commentService->delete($articleComment, $request->user());

        return response()->json([
            'status' => 'success',
            'message' => 'Article comment deleted successfully.',
            'data' => null,
        ]);
    }

    public function moderate(ModerateArticleCommentRequest $request, ArticleComment $articleComment): JsonResponse
    {
        $comment = $this->commentService->moderate($articleComment, $request->user(), (bool) ($request->validated()['is_approved'] ?? false));

        return response()->json([
            'status' => 'success',
            'message' => 'Article comment moderation updated successfully.',
            'data' => new ArticleCommentResource($comment),
        ]);
    }
}
