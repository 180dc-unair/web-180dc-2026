<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;

class UserService
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
    ) {
        //
    }

    /**
     * @return Collection<int, User>
     */
    public function getAll(): Collection
    {
        return $this->userRepository->allSorted();
    }

    /**
     * @param array<string, mixed> $data
     */
    public function create(array $data): User
    {
        return $this->userRepository->create($data);
    }

    /**
     * @param array<string, mixed> $data
     */
    public function update(User $user, array $data): User
    {
        if (($data['password'] ?? null) === null) {
            unset($data['password']);
        }

        return $this->userRepository->update($user, $data);
    }

    public function delete(User $user, User $currentUser): void
    {
        abort_if($currentUser->is($user), 422, 'Akun yang sedang digunakan tidak dapat dihapus.');

        $this->userRepository->delete($user);
    }
}
