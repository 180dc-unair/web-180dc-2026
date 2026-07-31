<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ProductService
{
    public function __construct(
        private readonly ProductRepositoryInterface $productRepository,
    ) {
        //
    }

    /**
     * @param array<string, mixed> $filters
     * @return Collection<int, Product>
     */
    public function getProducts(array $filters = [], bool $isAdmin = false): Collection
    {
        return $this->productRepository->allLatest($filters, $isAdmin);
    }

    public function getProductBySlug(string $slug, bool $isAdmin = false): ?Product
    {
        return $this->productRepository->findBySlug($slug, $isAdmin);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function createProduct(array $data): Product
    {
        return $this->productRepository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function updateProduct(Product $product, array $data): Product
    {
        return $this->productRepository->update($product, $data);
    }

    public function toggleProductBoolean(Product $product, string $field): Product
    {
        return $this->productRepository->toggleBoolean($product, $field);
    }

    public function deleteProduct(Product $product): void
    {
        $this->productRepository->delete($product);
    }
}