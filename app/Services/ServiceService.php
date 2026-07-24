<?php

namespace App\Services;

use App\Models\Service;
use App\Repositories\Contracts\ServiceRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;

class ServiceService
{
    public function __construct(
        private readonly ServiceRepositoryInterface $serviceRepository,
    ) {
        //
    }

    /**
     * @return Collection<int, Service>
     */
    public function getServices(): Collection
    {
        return $this->serviceRepository->allLatest();
    }

    /**
     * @param array{
     *      title: string,
     *      category_id?: string|null,
     *      icon_id?: string|null,
     *      short_description?: string|null,
     *      description?: string|null,
     *      is_featured?: bool,
     *      is_active?: bool,
     *      sort_order?: int,
     * } $data
     */
    public function createService(array $data): Service
    {
        $data['slug'] = Str::slug($data['title']);

        return $this->serviceRepository->create($data);
    }

    /**
     * @param array{
     *      title?: string,
     *      category_id?: string|null,
     *      icon_id?: string|null,
     *      short_description?: string|null,
     *      description?: string|null,
     *      is_featured?: bool,
     *      is_active?: bool,
     *      sort_order?: int,
     * } $data
     */
    public function updateService(Service $service, array $data): Service
    {
        if (isset($data['title'])) {
            $data['slug'] = Str::slug($data['title']);
        }

        return $this->serviceRepository->update($service, $data);
    }

    public function deleteService(Service $service): void
    {
        $this->serviceRepository->delete($service);
    }
}