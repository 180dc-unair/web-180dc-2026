<?php

namespace App\Http\Requests\TeamMember;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name') && ! $this->has('slug')) {
            $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
        } elseif ($this->has('slug')) {
            $this->merge(['slug' => Str::slug((string) $this->input('slug'))]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'min:1', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', 'unique:team_members,slug'],
            'image_id' => ['nullable', 'uuid', 'exists:media_assets,id'],
            'email' => ['nullable', 'email', 'max:255'],
            'linkedin_url' => ['nullable', 'url', 'max:2048'],
            'instagram_url' => ['nullable', 'url', 'max:2048'],
            'bio' => ['nullable', 'string', 'max:5000'],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'slug.required' => 'Slug wajib diisi.',
            'slug.regex' => 'Slug hanya boleh huruf kecil, angka, dan strip.',
            'slug.unique' => 'Slug sudah digunakan.',
            'image_id.uuid' => 'Image ID harus UUID valid.',
            'image_id.exists' => 'Gambar tidak ditemukan.',
            'email.email' => 'Format email tidak valid.',
            'linkedin_url.url' => 'LinkedIn URL harus URL valid.',
            'instagram_url.url' => 'Instagram URL harus URL valid.',
        ];
    }
}
