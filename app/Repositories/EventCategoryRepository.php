<?php

namespace App\Repositories;

use App\Models\EventCategory;
use App\Repositories\Contracts\EventCategoryRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class EventCategoryRepository implements EventCategoryRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = []): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min(100, $perPage));

        return EventCategory::query()
            ->withCount(['events'])
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('slug', 'like', "%{$search}%");
            }))
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                $allowed = ['name', 'created_at', 'slug'];
                if (in_array($sort, $allowed, true)) {
                    $q->orderBy($sort, $direction);
                }
            }, fn ($q) => $q->orderBy('name', 'asc'))
            ->paginate($perPage)
            ->appends($filters);
    }

    /**
     * @return Collection<int, EventCategory>
     */
    public function allLatest(?string $search = null): Collection
    {
        return EventCategory::query()
            ->withCount(['events'])
            ->when($search, fn ($q) => $q->where('name', 'like', "%{$search}%"))
            ->orderBy('name')
            ->get();
    }

    public function findBySlug(string $slug): ?EventCategory
    {
        return EventCategory::query()->withCount(['events'])->where('slug', $slug)->first();
    }

    public function findBySlugWithEvents(string $slug): ?EventCategory
    {
        return EventCategory::query()
            ->with(['events' => fn ($q) => $q->with(['category', 'image'])->latest()])
            ->withCount(['events'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): EventCategory
    {
        return EventCategory::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(EventCategory $category, array $data): EventCategory
    {
        $category->update($data);

        return $category->fresh();
    }

    public function delete(EventCategory $category): void
    {
        $category->delete();
    }
}
