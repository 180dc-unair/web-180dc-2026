<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateAdminUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        /** @var User $user */
        $user = $this->route('user');

        return [
            'name' => ['sometimes', 'string', 'max:255'],
            'username' => ['sometimes', 'string', 'max:255', 'alpha_dash', Rule::unique('users', 'username')->ignore($user)],
            'email' => ['sometimes', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['prohibited'],
            'password' => ['sometimes', 'nullable', 'string', 'min:8', 'max:255'],
        ];
    }
}
