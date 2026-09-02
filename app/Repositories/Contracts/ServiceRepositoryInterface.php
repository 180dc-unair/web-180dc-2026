<?php

namespace App\Repositories\Contracts;

use App\Models\Service;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface ServiceRepositoryInterface
{
    /**
     * @return Collection<int, Service>
     */
    public function allLatest(): Collection;

    /** @param array<string, mixed> $filters */
    public function paginate(array $filters = [], bool $includeAll = false): LengthAwarePaginator;

    public function findBySlug(string $slug): ?Service;

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
    public function create(array $data): Service;

    /**
     * @param array {
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
    public function update(Service $service, array $data): Service;

    public function delete(Service $service): void;
}
