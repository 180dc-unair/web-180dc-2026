<?php

namespace App\Services;

use App\Models\Client;
use App\Repositories\Contracts\ClientRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ClientService
{
    public function __construct(
        private readonly ClientRepositoryInterface $clientRepository,
    ) {
        //
    }

    /**
     * @return Collection<int, Client>
     */
    public function getClients(): Collection
    {
        return $this->clientRepository->allLatest();
    }

    /**
     * @param array{
     *      logo_id?: string|null,
     *      name: string,
     *      type: string,
     *      website_url?: string|null,
     *      is_featured?: bool,
     *      sort_order?: int,
     * } $data
     */
    public function createClient(array $data): Client
    {
        $data['slug'] = Str::slug($data['name']);

        return $this->clientRepository->create($data);
    }

    /**
     * @param array{
     *      logo_id?: string|null,
     *      name?: string,
     *      type?: string,
     *      website_url?: string|null,
     *      is_featured?: bool,
     *      sort_order?: int,
     * } $data
     */
    public function updateClient(Client $client, array $data): Client
    {
        if (isset($data['name'])) {
            $data['slug'] = Str::slug($data['name']);
        }

        return $this->clientRepository->update($client, $data);
    }

    public function deleteClient(Client $client): void
    {
        $this->clientRepository->delete($client);
    }
}