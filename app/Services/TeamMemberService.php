<?php

namespace App\Services;

use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeamMemberService
{
    public function __construct(
        private readonly TeamMemberRepositoryInterface $teamMemberRepository,
    ) {}

    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], bool $isAdmin = false): LengthAwarePaginator
    {
        return $this->teamMemberRepository->paginate($filters, $isAdmin);
    }

    public function findById(string $id, bool $isAdmin = false): ?TeamMember
    {
        return $this->teamMemberRepository->findById($id, $isAdmin);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TeamMember
    {
        return $this->teamMemberRepository->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(TeamMember $teamMember, array $data): TeamMember
    {
        return $this->teamMemberRepository->update($teamMember, $data);
    }

    public function delete(TeamMember $teamMember): void
    {
        $this->teamMemberRepository->delete($teamMember);
    }
}
