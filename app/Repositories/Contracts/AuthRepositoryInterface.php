<?php

namespace App\Repositories\Contracts;

use App\Models\User;

interface AuthRepositoryInterface
{
    public function findByEmail(string $email): ?User;

    /**
     * @param array{
     *      role_id: string,
     *      name: string,
     *      username: string,
     *      email: string,
     *      password: string,
     * } $data
     */
    public function create(array $data): User;
}