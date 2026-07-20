<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;

class AuthRepository implements AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User
    {
        return User::query()
            ->where('email', $email)
            ->first();
    }

    /**
     * @param array{
     *      role_id: string,
     *      name: string,
     *      username: string,
     *      email: string,
     *      password: string,
     * } $data
     */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }
}
