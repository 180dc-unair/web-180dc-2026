<?php

namespace App\Repositories;

use App\Models\Article;
use App\Repositories\Contracts\ArticleRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ArticleRepository implements ArticleRepositoryInterface
{
    public function allLatest(?string $search = null, ?string $categorySlug = null, bool $includeUnpublished = false): Collection
    {
        return Article::query()
            ->with(['author', 'category', 'thumbnail'])
            ->when(! $includeUnpublished, fn ($query) => $query->where('status', 'published'))
            ->when($search, fn ($query) => $query->where(function ($query) use ($search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('excerpt', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            }))
            ->when($categorySlug, fn ($query) => $query->whereHas(
                'category',
                fn ($category) => $category->where('slug', $categorySlug),
            ))
            ->orderByDesc('published_at')
            ->latest()
            ->get();
    }

    public function findBySlug(string $slug, bool $includeUnpublished = false): ?Article
    {
        return Article::query()
            ->with(['author', 'category', 'thumbnail'])
            ->when(! $includeUnpublished, fn ($query) => $query->where('status', 'published'))
            ->where('slug', $slug)
            ->first();
    }

    public function create(array $data): Article
    {
        return Article::query()->create($data)->load(['author', 'category', 'thumbnail']);
    }

    public function update(Article $article, array $data): Article
    {
        $article->update($data);

        return $article->fresh(['author', 'category', 'thumbnail']);
    }

    public function delete(Article $article): void
    {
        $article->delete();
    }

    public function incrementViews(Article $article): Article
    {
        $article->increment('view_count');

        return $article->fresh(['author', 'category', 'thumbnail']);
    }
}
