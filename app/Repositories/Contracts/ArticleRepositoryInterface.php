<?php

namespace App\Repositories\Contracts;

use App\Models\Article;
use Illuminate\Database\Eloquent\Collection;

interface ArticleRepositoryInterface
{
    /** @return Collection<int, Article> */
    public function allLatest(?string $search = null, ?string $categorySlug = null, bool $includeUnpublished = false): Collection;

    public function findBySlug(string $slug, bool $includeUnpublished = false): ?Article;

    public function create(array $data): Article;

    public function update(Article $article, array $data): Article;

    public function delete(Article $article): void;

    public function incrementViews(Article $article): Article;
}
