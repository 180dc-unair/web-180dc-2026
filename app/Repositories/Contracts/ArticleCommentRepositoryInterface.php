<?php

namespace App\Repositories\Contracts;

use App\Models\Article;
use App\Models\ArticleComment;
use Illuminate\Database\Eloquent\Collection;

interface ArticleCommentRepositoryInterface
{
    /** @return Collection<int, ArticleComment> */
    public function allForArticle(Article $article, bool $includeUnapproved = false): Collection;

    /** @return Collection<int, ArticleComment> */
    public function allForAdmin(?string $search = null, ?bool $isApproved = null): Collection;

    public function create(array $data): ArticleComment;

    public function update(ArticleComment $comment, array $data): ArticleComment;

    public function delete(ArticleComment $comment): void;
}
