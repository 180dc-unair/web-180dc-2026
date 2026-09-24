<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductRepository implements ProductRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Product>
     */
    public function allLatest(array $filters = [], bool $includeInactive = false): Collection
    {
        return Product::query()
            ->with(['category', 'image'])
            ->when(! $includeInactive, fn ($q) => $q->where('status', 'active'))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('title', 'like', "%{$search}%"))
            ->when($filters['category_id'] ?? null, fn ($q, $categoryId) => $q->where('category_id', $categoryId))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when(isset($filters['is_featured']), fn ($q) => $q->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN)))
            ->when(isset($filters['is_best_seller']), fn ($q) => $q->where('is_best_seller', filter_var($filters['is_best_seller'], FILTER_VALIDATE_BOOLEAN)))
            ->when($filters['sort_by'] ?? null, function ($q, $sortBy) use ($filters) {
                $direction = ($filters['sort_direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                $allowed = ['price', 'created_at', 'sold_count'];

                if (in_array($sortBy, $allowed)) {
                    $q->orderBy($sortBy, $direction);
                }
            }, fn ($q) => $q->latest())
            ->get();
    }

    public function findBySlug(string $slug, bool $includeInactive = false): ?Product
    {
        return Product::query()
            ->with(['category', 'image'])
            ->when(! $includeInactive, fn ($q) => $q->where('status', 'active'))
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product
    {
        $product->update($data);

        return $product->fresh();
    }

    public function toggleBoolean(Product $product, string $field): Product
    {
        $allowed = ['is_best_seller', 'is_featured'];

        if (! in_array($field, $allowed, true)) {
            throw new \InvalidArgumentException('Field tidak dapat di-toggle.');
        }

        $product->update([
            $field => ! $product->{$field},
        ]);

        return $product->fresh();
    }

    public function delete(Product $product): void
    {
        $product->delete();
    }

    /**
     * @param array<int, string> $ids
     * @return Collection<int, Product>
     */
    public function lockForSale(array $ids): Collection
    {
        return Product::query()->whereIn('id', $ids)->orderBy('id')->lockForUpdate()->get()->keyBy('id');
    }

    public function recordSale(Product $product, int $quantity): void
    {
        if ($product->type === 'physical') {
            $product->decrement('stock', $quantity);
        }

        $product->increment('sold_count', $quantity);
    }
}
