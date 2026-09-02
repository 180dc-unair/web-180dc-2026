<?php

namespace App\Services;

use App\Models\ArticleCategory;
use App\Repositories\Contracts\ArticleCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ArticleCategoryService
{
    public function __construct(
        private readonly ArticleCategoryRepositoryInterface $repository,
    ) {}

    public function getCategories(?string $search = null): Collection
    {
        return $this->repository->allLatest($search);
    }

    public function getBySlug(string $slug): ?ArticleCategory
    {
        return $this->repository->findBySlug($slug);
    }

    public function create(array $data): ArticleCategory
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $this->repository->create($data);
    }

    public function update(ArticleCategory $category, array $data): ArticleCategory
    {
        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->repository->update($category, $data);
    }

    public function delete(ArticleCategory $category): void
    {
        $this->repository->delete($category);
    }
}
