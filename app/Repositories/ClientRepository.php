<?php

namespace App\Repositories;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
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