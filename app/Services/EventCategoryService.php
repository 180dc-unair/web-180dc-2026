<?php

namespace App\Services;

use App\Models\EventCategory;
use App\Repositories\Contracts\EventCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventCategoryService
{
    public function __construct(
        private readonly EventCategoryRepositoryInterface $eventCategoryRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        return $this->eventCategoryRepository->paginate($filters);
    }

    public function allLatest(?string $search = null): Collection
    {
        return $this->eventCategoryRepository->allLatest($search);
    }

    public function findBySlug(string $slug): ?EventCategory
    {
        return $this->eventCategoryRepository->findBySlug($slug);
    }

    public function findBySlugWithEvents(string $slug): ?EventCategory
    {
        return $this->eventCategoryRepository->findBySlugWithEvents($slug);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): EventCategory
    {
        return $this->eventCategoryRepository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(EventCategory $category, array $data): EventCategory
    {
        return $this->eventCategoryRepository->update($category, $data);
    }

    public function delete(EventCategory $category): void
    {
        $this->eventCategoryRepository->delete($category);
    }
}
