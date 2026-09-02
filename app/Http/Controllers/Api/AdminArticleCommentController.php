<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleCommentResource;
use App\Services\ArticleCommentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminArticleCommentController extends Controller
{
    public function __construct(
        private readonly ArticleCommentService $commentService,
    ) {}

    public function index(Request $request): JsonResponse
    {
        $isApproved = $request->has('is_approved')
            ? $request->boolean('is_approved')
            : null;

        return response()->json([
            'status' => 'success',
            'message' => 'Article comments retrieved successfully.',
            'data' => ArticleCommentResource::collection(
                $this->commentService->getAdminComments(
                    $request->query('search'),
                    $isApproved,
                ),
            ),
        ]);
    }
}
