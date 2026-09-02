<?php

namespace App\Services;

use App\Models\ServiceCategory;
use App\Repositories\Contracts\ServiceCategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ServiceCategoryService
{
    public function __construct(
        private readonly ServiceCategoryRepositoryInterface $repository,
    ) {}

    public function getCategories(?string $search = null): Collection
    {
        return $this->repository->allLatest($search);
    }

    public function getBySlug(string $slug): ?ServiceCategory
    {
        return $this->repository->findBySlug($slug);
    }

    public function getBySlugWithServices(string $slug): ?ServiceCategory
    {
        return $this->repository->findBySlugWithServices($slug);
    }

    public function create(array $data): ServiceCategory
    {
        $data['slug'] = $data['slug'] ?? Str::slug($data['name']);

        return $this->repository->create($data);
    }

    public function update(ServiceCategory $category, array $data): ServiceCategory
    {
        if (isset($data['name']) && ! isset($data['slug'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->repository->update($category, $data);
    }

    public function delete(ServiceCategory $category): void
    {
        $this->repository->delete($category);
    }
}
