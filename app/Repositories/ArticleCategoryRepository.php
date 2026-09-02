<?php

namespace App\Repositories;

use App\Models\ArticleCategory;
use App\Repositories\Contracts\ArticleCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ArticleCategoryRepository implements ArticleCategoryRepositoryInterface
{
    public function allLatest(?string $search = null): Collection
    {
        return ArticleCategory::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->latest()
            ->get();
    }

    public function findBySlug(string $slug): ?ArticleCategory
    {
        return ArticleCategory::query()->where('slug', $slug)->first();
    }

    public function create(array $data): ArticleCategory
    {
        return ArticleCategory::query()->create($data);
    }

    public function update(ArticleCategory $category, array $data): ArticleCategory
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(ArticleCategory $category): void
    {
        $category->delete();
    }
}
