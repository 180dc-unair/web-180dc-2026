<?php

namespace App\Repositories;

use App\Models\ServiceCategory;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ServiceCategoryRepository implements ServiceCategoryRepositoryInterface
{
    public function allLatest(?string $search = null): Collection
    {
        return ServiceCategory::query()
            ->when($search, fn ($query) => $query->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->latest()
            ->get();
    }

    public function findBySlug(string $slug): ?ServiceCategory
    {
        return ServiceCategory::query()->where('slug', $slug)->first();
    }

    public function findBySlugWithServices(string $slug): ?ServiceCategory
    {
        return ServiceCategory::query()
            ->where('slug', $slug)
            ->with(['services' => fn ($query) => $query
                ->with(['category', 'icon'])
                ->orderBy('sort_order')
                ->latest(),
            ])
            ->first();
    }

    public function create(array $data): ServiceCategory
    {
        return ServiceCategory::query()->create($data);
    }

    public function update(ServiceCategory $category, array $data): ServiceCategory
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(ServiceCategory $category): void
    {
        $category->delete();
    }
}
