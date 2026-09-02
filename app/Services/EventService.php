<?php

namespace App\Services;

use App\Models\Event;
use App\Repositories\Contracts\EventRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EventService
{
    public function __construct(
        private readonly EventRepositoryInterface $eventRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], bool $isAdmin = false): LengthAwarePaginator
    {
        return $this->eventRepository->paginate($filters, $isAdmin);
    }

    public function findBySlug(string $slug, bool $isAdmin = false): ?Event
    {
        return $this->eventRepository->findBySlug($slug, $isAdmin);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Event
    {
        return $this->eventRepository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(Event $event, array $data): Event
    {
        return $this->eventRepository->update($event, $data);
    }

    public function delete(Event $event): void
    {
        $this->eventRepository->delete($event);
    }
}
