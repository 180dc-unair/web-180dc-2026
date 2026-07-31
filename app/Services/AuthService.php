<?php

namespace App\Services;

use App\Models\User;
use App\Repositories\Contracts\AuthRepositoryInterface;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function __construct(
        private readonly AuthRepositoryInterface $authRepository,
    ) {
        //
    }

    /**
     * @param array{
     *      name: string,
     *      username: string,
     *      email: string,
     *      password: string,
     * } $data
     * @return array{user: User, token: string}
     */
    public function register(array $data): array
    {
        $user = $this->authRepository->create([
            'role' => 'user',
            'name' => $data['name'],
            'email' => $data['email'],
            'username' => $data['username'],
            'password' => $data['password'],
        ]);

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token, 
        ];
    }

    /**
     * @param array{email: string, password: string} $data
     * @return array{user: User, token: string,}
     * 
     * @throws ValidationException
     */
    public function login(array $data): array
    {
        $user = $this->authRepository->findByEmail($data['email']);

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()->delete();
    }
}