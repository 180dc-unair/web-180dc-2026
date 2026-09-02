<?php

namespace App\Repositories;

use App\Models\TeamMember;
use App\Repositories\Contracts\TeamMemberRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class TeamMemberRepository implements TeamMemberRepositoryInterface
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function paginate(array $filters = [], bool $includeInactive = false): LengthAwarePaginator
    {
        $perPage = (int) ($filters['per_page'] ?? 15);
        $perPage = max(1, min(100, $perPage));

        return TeamMember::query()
            ->with(['image'])
            ->when(! $includeInactive, fn ($q) => $q->where('is_active', true))
            ->when($includeInactive && isset($filters['is_active']), function ($q) use ($filters) {
                $val = filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
                if ($val !== null) {
                    $q->where('is_active', $val);
                }
            })
            ->when($filters['search'] ?? null, function ($q, $search) {
                $q->where(function ($qq) use ($search) {
                    $qq->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('bio', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($filters['sort'] ?? null, function ($q, $sort) use ($filters) {
                $direction = ($filters['direction'] ?? 'asc') === 'desc' ? 'desc' : 'asc';
                $allowed = ['name', 'created_at', 'slug'];
                if (in_array($sort, $allowed, true)) {
                    $q->orderBy($sort, $direction);
                }
            }, fn ($q) => $q->orderBy('name', 'asc'))
            ->paginate($perPage)
            ->appends($filters);
    }

    public function findById(string $id, bool $includeInactive = false): ?TeamMember
    {
        return TeamMember::query()
            ->with(['image'])
            ->when(! $includeInactive, fn ($q) => $q->where('is_active', true))
            ->where('id', $id)
            ->first();
    }

    public function findByIdWithInactive(string $id): ?TeamMember
    {
        return TeamMember::query()->with(['image'])->where('id', $id)->first();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): TeamMember
    {
        return TeamMember::query()->create($data);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function update(TeamMember $teamMember, array $data): TeamMember
    {
        $teamMember->update($data);

        return $teamMember->fresh(['image']);
    }

    public function delete(TeamMember $teamMember): void
    {
        $teamMember->delete();
    }
}
