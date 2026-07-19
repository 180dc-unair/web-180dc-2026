<?php

namespace App\Repositories;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class ServiceRepository implements ServiceRepositoryInterface
{
    /**
     * @return Collection<int, Service>
     */
    public function allLatest(): Collection
    {
        return Service::query()
            ->with(['category', 'icon'])
            ->orderBy('sort_order')
            ->latest()
            ->get();
    }

    /**
     * @param array{
     *      category_id?: string|null,
     *      icon_id?: string|null,
     *      title: string,
     *      slug: string,
     *      short_description?: string|null,
     *      description?: string|null,
     *      is_featured?: bool,
     *      is_active?: bool,
     *      sort_order?: int,
     * } $data
     */
    public function create(array $data): Service
    {
        return Service::query()->create($data);
    }

    /**
     * @param array{
     *      category_id?: string|null,
     *      icon_id?: string|null,
     *      title?: string,
     *      slug?: string,
     *      short_description?: string|null,
     *      description?: string|null,
     *      is_featured?: bool,
     *      is_active?: bool,
     *      sort_order?: int,
     * } $data
     */
    public function update(Service $service, array $data): Service
    {
        $service->update($data);

        return $service->fresh();
    }

    public function delete(Service $service): void
    {
        $service->delete();
    }
}