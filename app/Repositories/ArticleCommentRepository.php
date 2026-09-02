<?php

namespace App\Repositories;

use App\Models\Article;
use App\Models\ArticleComment;
use App\Repositories\Contracts\ArticleCommentRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ArticleCommentRepository implements ArticleCommentRepositoryInterface
{
    public function allForArticle(Article $article, bool $includeUnapproved = false): Collection
    {
        return ArticleComment::query()
            ->where('article_id', $article->id)
            ->whereNull('parent_id')
            ->when(! $includeUnapproved, fn ($query) => $query->where('is_approved', true))
            ->with([
                'user',
                'replies' => fn ($query) => $query
                    ->with('user')
                    ->when(! $includeUnapproved, fn ($query) => $query->where('is_approved', true))
                    ->latest(),
            ])
            ->latest()
            ->get();
    }

    public function allForAdmin(?string $search = null, ?bool $isApproved = null): Collection
    {
        return ArticleComment::query()
            ->with(['user', 'article'])
            ->when($search, fn ($query) => $query->where(
                'content',
                'like',
                "%{$search}%",
            ))
            ->when($isApproved !== null, fn ($query) => $query->where('is_approved', $isApproved))
            ->latest()
            ->get();
    }

    public function create(array $data): ArticleComment
    {
        return ArticleComment::query()->create($data)->load('user');
    }

    public function update(ArticleComment $comment, array $data): ArticleComment
    {
        $comment->update($data);

        return $comment->fresh(['user', 'replies.user']);
    }

    public function delete(ArticleComment $comment): void
    {
        $comment->delete();
    }
}
