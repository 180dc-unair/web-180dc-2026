<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreAdminUserRequest;
use App\Http\Requests\Admin\UpdateAdminUserRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminUserController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'message' => 'Users retrieved successfully.',
            'data' => UserResource::collection(User::query()->orderBy('name')->get()),
        ]);
    }

    public function store(StoreAdminUserRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['role'] = 'user';

        $user = User::query()->create($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User created successfully.',
            'data' => new UserResource($user),
        ], 201);
    }

    public function update(UpdateAdminUserRequest $request, User $user): JsonResponse
    {
        $data = $request->validated();

        if (($data['password'] ?? null) === null) {
            unset($data['password']);
        }

        $user->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'User updated successfully.',
            'data' => new UserResource($user->refresh()),
        ]);
    }

    public function destroy(Request $request, User $user): JsonResponse
    {
        if ($request->user()?->is($user)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Akun yang sedang digunakan tidak dapat dihapus.',
                'data' => null,
            ], 422);
        }

        $user->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'User deleted successfully.',
            'data' => null,
        ]);
    }
}
