<?php

namespace App\Repositories\Contracts;

use App\Models\ProductCategory;
use Illuminate\Database\Eloquent\Collection;

interface ProductCategoryRepositoryInterface
{
    /**
     * @return Collection<int, ProductCategory>
     */
    public function allLatest(?string $search = null): Collection;

    public function findBySlug(string $slug): ?ProductCategory;

    /**
     * @param array{
     *      name: string,
     *      slug: string,
     *      sort_order?: int
     * } $data
     */
    public function create(array $data): ProductCategory;

    /**
     * @param array{
     *      name?: string,
     *      slug?: string,
     *      sort_order?: int
     * } $data
     */
    public function update(ProductCategory $productCategory, array $data): ProductCategory;

    public function delete(ProductCategory $productCategory): void;
}
