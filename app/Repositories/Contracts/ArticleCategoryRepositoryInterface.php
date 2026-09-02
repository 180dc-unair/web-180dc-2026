<?php

namespace App\Repositories\Contracts;

use App\Models\ArticleCategory;
use Illuminate\Database\Eloquent\Collection;

interface ArticleCategoryRepositoryInterface
{
    /** @return Collection<int, ArticleCategory> */
    public function allLatest(?string $search = null): Collection;

    public function findBySlug(string $slug): ?ArticleCategory;

    public function create(array $data): ArticleCategory;

    public function update(ArticleCategory $category, array $data): ArticleCategory;

    public function delete(ArticleCategory $category): void;
}
