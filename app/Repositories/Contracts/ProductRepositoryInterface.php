<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Database\Eloquent\Collection;

interface ProductRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     * @return Collection<int, Product>
     */
    public function allLatest(array $filters = [], bool $includeInactive = false): Collection;

    public function findBySlug(string $slug, bool $includeInactive = false): ?Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Product;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Product $product, array $data): Product;

    public function toggleBoolean(Product $product, string $field): Product;

    /**
     * @param array<int, string> $ids
     * @return Collection<int, Product>
     */
    public function lockForSale(array $ids): Collection;

    public function recordSale(Product $product, int $quantity): void;

    public function delete(Product $product): void;
}
