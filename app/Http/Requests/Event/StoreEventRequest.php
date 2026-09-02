<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'slug' => ['required', 'string', 'unique:events,slug'],
            'category_id' => ['nullable', 'uuid', 'exists:event_categories,id'],
            'image_id' => ['nullable', 'uuid', 'exists:media_assets,id'],
            'description' => ['nullable', 'string'],
            'location' => ['nullable', 'string', 'max:255'],
            'start_at' => ['nullable', 'date'],
            'end_at' => ['nullable', 'date', 'after_or_equal:start_at'],
            'gdoc_url' => ['nullable', 'url', 'max:2048'],
            'status' => ['nullable', 'string', 'in:active,inactive,draft'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'is_paid' => ['nullable', 'boolean'],
            'is_featured' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Judul wajib diisi.',
            'slug.unique' => 'Slug sudah digunakan.',
            'category_id.exists' => 'Kategori tidak ditemukan.',
            'image_id.exists' => 'Gambar tidak ditemukan.',
            'end_at.after_or_equal' => 'Tanggal selesai harus setelah tanggal mulai.',
            'gdoc_url.url' => 'GDoc URL harus URL valid.',
            'status.in' => 'Status harus active, inactive, atau draft.',
        ];
    }
}
