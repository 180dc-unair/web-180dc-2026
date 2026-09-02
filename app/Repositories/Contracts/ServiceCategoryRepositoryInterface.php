<?php

namespace App\Repositories\Contracts;

use App\Models\ServiceCategory;
use Illuminate\Database\Eloquent\Collection;

interface ServiceCategoryRepositoryInterface
{
    /** @return Collection<int, ServiceCategory> */
    public function allLatest(?string $search = null): Collection;

    public function findBySlug(string $slug): ?ServiceCategory;

    public function findBySlugWithServices(string $slug): ?ServiceCategory;

    public function create(array $data): ServiceCategory;

    public function update(ServiceCategory $category, array $data): ServiceCategory;

    public function delete(ServiceCategory $category): void;
}
