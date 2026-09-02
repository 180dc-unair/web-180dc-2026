<?php

namespace App\Repositories\Contracts;

use App\Models\Client;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ClientRepositoryInterface
{
    /**
     * @return Collection<int, Client>
     */
    public function allLatest(): Collection;

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], bool $includeAll = false): LengthAwarePaginator;

    public function findBySlug(string $slug): ?Client;

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
    public function create(array $data): Client;

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
    public function update(Client $client, array $data): Client;

    public function delete(Client $client): void;
}
