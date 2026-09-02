<?php

namespace App\Repositories;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository implements ServiceRepositoryInterface
{
    /**
     * @return Collection<int, Service>
     */
    public function allLatest(): Collection
    {
        return Service::query()
            ->with(['category', 'icon'])
            ->orderBy('sort_order')
            ->latest()
            ->get();
    }

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], bool $includeAll = false): LengthAwarePaginator
    {
        return Service::query()
            ->with(['category', 'icon'])
            ->when(! $includeAll, fn ($q) => $q->where('is_active', true))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($filters['category_id'] ?? null, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when(($filters['sort'] ?? null) === 'title', fn ($q) => $q->orderBy('title', $filters['direction'] ?? 'asc'))
            ->unless($filters['sort'] ?? null, fn ($q) => $q->orderBy('sort_order')->latest())
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findBySlug(string $slug): ?Service
    {
        return Service::query()
            ->with(['category', 'icon'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param array{
     *      category_id?: string|null,
     *      icon_id?: string|null,
     *      title: string,
     *      slug: string,
     *      short_description?: string|null,
     *      description?: string|null,
     *      is_featured?: bool,
     *      is_active?: bool,
     *      sort_order?: int,
     * } $data
     */
    public function create(array $data): Service
    {
        return Service::query()->create($data);
    }

    /**
     * @param array{
     *      category_id?: string|null,
     *      icon_id?: string|null,
     *      title?: string,
     *      slug?: string,
     *      short_description?: string|null,
     *      description?: string|null,
     *      is_featured?: bool,
     *      is_active?: bool,
     *      sort_order?: int,
     * } $data
     */
    public function update(Service $service, array $data): Service
    {
        $service->update($data);

        return $service->fresh();
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }
}
