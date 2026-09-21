<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserRepository implements UserRepositoryInterface
{
    /**
     * @return Collection<int, User>
     */
    public function allSorted(): Collection
    {
        return User::query()->orderBy('name')->get();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): User
    {
        $user->update($data);

        return $user->fresh();
    }

    public function delete(User $user): void
    {
        $user->delete();
    }
}
