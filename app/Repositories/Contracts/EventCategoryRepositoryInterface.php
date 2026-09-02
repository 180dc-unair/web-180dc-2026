<?php

namespace App\Repositories\Contracts;

use App\Models\EventCategory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface EventCategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator;

    /**
     * @return Collection<int, EventCategory>
     */
    public function allLatest(?string $search = null): Collection;

    public function findBySlug(string $slug): ?EventCategory;

    public function findBySlugWithEvents(string $slug): ?EventCategory;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): EventCategory;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(EventCategory $category, array $data): EventCategory;

    public function delete(EventCategory $category): void;
}
