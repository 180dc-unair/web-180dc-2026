<?php

namespace App\Services;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ArticleService
{
    public function __construct(
        private readonly ArticleRepositoryInterface $repository,
    ) {}

    public function getArticles(?string $search = null, ?string $categorySlug = null, bool $includeUnpublished = false): Collection
    {
        return $this->repository->allLatest($search, $categorySlug, $includeUnpublished);
    }

    public function getBySlug(string $slug, bool $includeUnpublished = false, bool $incrementView = false): ?Article
    {
        $article = $this->repository->findBySlug($slug, $includeUnpublished);

        if ($article && $incrementView) {
            return $this->repository->incrementViews($article);
        }

        return $article;
    }

    public function create(array $data, string $authorId): Article
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['title']);
        $data['author_id'] = $authorId;
        $this->normalizePublication($data);

        return $this->repository->create($data);
    }

    public function update(Article $article, array $data): Article
    {
        if (isset($data['title']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['title']);
        }
        $this->normalizePublication($data, $article);

        return $this->repository->update($article, $data);
    }

    public function delete(Article $article): void
    {
        $this->repository->delete($article);
    }

    /** @param array<string, mixed> $data */
    private function normalizePublication(array &$data, ?Article $article = null): void
    {
        $status = $data['status'] ?? $article?->status ?? 'draft';

        if ($status === 'published' && empty($data['published_at']) && ! $article?->published_at) {
            $data['published_at'] = now();
        }

        if ($status !== 'published') {
            $data['published_at'] = null;
        }
    }
}
