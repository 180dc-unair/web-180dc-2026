<?php

namespace App\Http\Requests\TeamMember;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateTeamMemberRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name') && ! $this->has('slug') && $this->input('name')) {
            $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
        } elseif ($this->has('slug') && $this->input('slug')) {
            $this->merge(['slug' => Str::slug((string) $this->input('slug'))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $teamMemberId = $this->route('teamMember')?->id ?? $this->route('team_member')?->id ?? null;

        return [
            'name' => ['sometimes', 'required', 'string', 'min:1', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', 'max:255', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('team_members', 'slug')->ignore($teamMemberId)],
            'image_id' => ['sometimes', 'nullable', 'uuid', 'exists:media_assets,id'],
            'email' => ['sometimes', 'nullable', 'email', 'max:255'],
            'linkedin_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'instagram_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'bio' => ['sometimes', 'nullable', 'string', 'max:5000'],
            'is_active' => ['sometimes', 'nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Nama wajib diisi.',
            'slug.regex' => 'Slug hanya boleh huruf kecil, angka, dan strip.',
            'slug.unique' => 'Slug sudah digunakan.',
            'image_id.uuid' => 'Image ID harus UUID valid.',
            'image_id.exists' => 'Gambar tidak ditemukan.',
        ];
    }
}
