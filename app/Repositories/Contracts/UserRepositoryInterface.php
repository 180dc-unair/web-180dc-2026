<?php

namespace App\Repositories\Contracts;

use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface UserRepositoryInterface
{
    /**
     * @return Collection<int, User>
     */
    public function allSorted(): Collection;

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): User;

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): User;

    public function delete(User $user): void;
}
