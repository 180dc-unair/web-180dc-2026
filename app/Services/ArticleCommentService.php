<?php

namespace App\Services;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Models\User;
use App\Repositories\Contracts\ArticleCommentRepositoryInterface;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

class ArticleCommentService
{
    public function __construct(
        private readonly ArticleCommentRepositoryInterface $repository,
    ) {}

    public function getComments(Article $article, bool $includeUnapproved = false): Collection
    {
        return $this->repository->allForArticle($article, $includeUnapproved);
    }

    public function getAdminComments(?string $search = null, ?bool $isApproved = null): Collection
    {
        return $this->repository->allForAdmin($search, $isApproved);
    }

    public function create(Article $article, User $user, array $data): ArticleComment
    {
        $this->validateParent($article, $data['parent_id'] ?? null);

        return $this->repository->create([
            'article_id' => $article->id,
            'user_id' => $user->id,
            'parent_id' => $data['parent_id'] ?? null,
            'content' => $data['content'],
            'is_approved' => true,
        ]);
    }

    public function update(ArticleComment $comment, User $user, array $data): ArticleComment
    {
        $this->ensureCanManage($comment, $user);

        return $this->repository->update($comment, ['content' => $data['content']]);
    }

    public function delete(ArticleComment $comment, User $user): void
    {
        $this->ensureCanManage($comment, $user);
        $this->repository->delete($comment);
    }

    public function moderate(ArticleComment $comment, bool $isApproved): ArticleComment
    {
        return $this->repository->update($comment, ['is_approved' => $isApproved]);
    }

    private function ensureCanManage(ArticleComment $comment, User $user): void
    {
        if ($user->role !== 'admin' && $comment->user_id !== $user->id) {
            throw new AuthorizationException('You are not allowed to manage this comment.');
        }
    }

    private function validateParent(Article $article, ?string $parentId): void
    {
        if (! $parentId) {
            return;
        }

        $parent = ArticleComment::query()
            ->whereKey($parentId)
            ->where('article_id', $article->id)
            ->whereNull('parent_id')
            ->first();

        if (! $parent) {
            throw ValidationException::withMessages([
                'parent_id' => 'The parent comment must belong to this article and be a root comment.',
            ]);
        }
    }
}
