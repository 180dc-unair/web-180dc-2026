<?php

namespace App\Repositories\Contracts;

use App\Models\TeamMember;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface TeamMemberRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], bool $includeInactive = false): LengthAwarePaginator;

    public function findById(string $id, bool $includeInactive = false): ?TeamMember;

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TeamMember;

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(TeamMember $teamMember, array $data): TeamMember;

    public function delete(TeamMember $teamMember): void;
}
