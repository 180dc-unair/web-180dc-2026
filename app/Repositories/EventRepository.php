<?php

namespace App\Repositories;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventRepository implements EventRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], bool $includeInactive = false): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 12);
        $perPage = max(1, min(100, $perPage));

        return Event::query()
            ->with(['category', 'image'])
            ->when(! $includeInactive && ! isset($filters['status']), fn ($q) => $q->where('status', 'active'))
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('title', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhere('location', 'like', "%{$search}%");
                });
            })
            ->when($filters['category_id'] ?? null, fn ($q, $cat) => $q->where('category_id', $cat))
            ->when($filters['category_slug'] ?? null, function ($q, $slug) {
                $q->whereHas('category', fn ($qq) => $qq->where('slug', $slug));
            })
            ->when(isset($filters['is_featured']), fn ($q) => $q->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN)))
            ->when(isset($filters['is_paid']), fn ($q) => $q->where('is_paid', filter_var($filters['is_paid'], FILTER_VALIDATE_BOOLEAN)))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $direction = ($filters['direction'] ?? 'desc') === 'desc' ? 'desc' : 'asc';
                $allowed = ['title', 'start_at', 'created_at', 'price'];
                if (in_array($sort, $allowed, true)) {
                    $q->orderBy($sort, $direction);
                }
            }, function ($q) use ($filters) {
                // default: featured first, then start_at desc, then latest
                if (! isset($filters['sort'])) {
                    $q->orderBy('is_featured', 'desc')->orderBy('start_at', 'desc')->latest();
                }
            })
            ->paginate($perPage)
            ->appends($filters);
    }

    public function findBySlug(string $slug, bool $includeInactive = false): ?Event
    {
        return Event::query()
            ->with(['category', 'image'])
            ->when(! $includeInactive, fn ($q) => $q->where('status', 'active'))
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Event
    {
        return Event::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Event $event, array $data): Event
    {
        $event->update($data);

        return $event->fresh(['category', 'image']);
    }

    public function delete(Event $event): void
    {
        $event->delete();
    }
}
