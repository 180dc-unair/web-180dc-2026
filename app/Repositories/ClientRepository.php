<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class ClientRepository implements ClientRepositoryInterface
{
    /**
     * @return Collection<int, Client>
     */
    public function allLatest(): Collection
    {
        return Client::query()
            ->with(['logo'])
            ->orderBy('sort_order')
            ->latest()
            ->get();
    }

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], bool $includeAll = false): LengthAwarePaginator
    {
        return Client::query()
            ->with(['logo'])
            ->when(! $includeAll, fn ($q) => $q->where('is_active', true))
            ->when($filters['search'] ?? null, fn ($q, $search) => $q->where('name', 'like', "%{$search}%"))
            ->when($filters['type'] ?? null, fn ($q, $type) => $q->where('type', $type))
            ->when(isset($filters['is_featured']), fn ($q) => $q->where('is_featured', filter_var($filters['is_featured'], FILTER_VALIDATE_BOOLEAN)))
            ->when(($filters['sort'] ?? null) === 'name', fn ($q) => $q->orderBy('name', $filters['direction'] ?? 'asc'))
            ->unless($filters['sort'] ?? null, fn ($q) => $q->orderBy('sort_order')->latest())
            ->paginate($filters['per_page'] ?? 15);
    }

    public function findBySlug(string $slug): ?Client
    {
        return Client::query()
            ->with(['logo'])
            ->where('slug', $slug)
            ->first();
    }

    /**
     * @param array{
     *      logo_id?: string|null,
     *      name: string,
     *      slug: string,
     *      type: string,
     *      website_url?: string|null,
     *      is_featured?: bool,
     *      sort_order?: int,
     * } $data
     */
    public function create(array $data): Client
    {
        return Client::query()->create($data);
    }

    /**
     * @param array{
     *     logo_id?: string|null,
     *     name?: string,
     *     slug?: string,
     *     type?: string,
     *     website_url?: string|null,
     *     is_featured?: bool,
     *     sort_order?: int,
     * } $data
     */
    public function update(Client $client, array $data): Client
    {
        $client->update($data);

        return $client->fresh();
    }

    public function delete(Client $client): void
    {
        $client->delete();
    }
}
