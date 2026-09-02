<?php

namespace App\Repositories;

use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductCategoryRepository implements ProductCategoryRepositoryInterface
{
    /**
     * @return Collection<int, ProductCategory>
     */
    public function allLatest(?string $search = null): Collection
    {
        return ProductCategory::query()
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('sort_order')
            ->latest()
            ->get();
    }

    public function findBySlug(string $slug): ?ProductCategory
    {
        return ProductCategory::query()
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param  array{name: string, slug:string, sort_order?: int}  $data
     */
    public function create(array $data): ProductCategory
    {
        return ProductCategory::query()->create($data);
    }

    /**
     * @param  array{name?: string, slug:string, sort_order?: int}  $data
     */
    public function update(ProductCategory $productCategory, array $data): ProductCategory
    {
        $productCategory->update($data);

        return $productCategory->fresh();
    }

    public function delete(ProductCategory $productCategory): void
    {
        $productCategory->delete();
    }
}
