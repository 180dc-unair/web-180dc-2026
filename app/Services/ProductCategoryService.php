<?php

namespace App\Services;

use App\Models\ProductCategory;
use App\Repositories\Contracts\ProductCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductCategoryService
{
    public function __construct(
        private readonly ProductCategoryRepositoryInterface $productCategoryRepository,
    ) {
        //
    }

    /**
     * @return Collection<int, ProductCategory>
     */
    public function getProductCategories(?string $search = null): Collection
    {
        return $this->productCategoryRepository->allLatest($search);
    }

    public function getProductCategoryBySlug(string $slug): ?ProductCategory
    {
        return $this->productCategoryRepository->findBySlug($slug);
    }

    /**
     * @param  array{name: string, sort_order?: int}  $data
     */
    public function createProductCategory(array $data): ProductCategory
    {
        return $this->productCategoryRepository->create($data);
    }

    /**
     * @param  array{name?: string, sort_order?: int}  $data
     */
    public function updateProductCategory(ProductCategory $productCategory, array $data): ProductCategory
    {
        return $this->productCategoryRepository->update($productCategory, $data);
    }

    public function deleteProductCategory(ProductCategory $productCategory): void
    {
        $this->productCategoryRepository->delete($productCategory);
    }
}
