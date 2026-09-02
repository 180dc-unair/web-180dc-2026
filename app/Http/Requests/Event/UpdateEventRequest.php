<?php

namespace App\Http\Requests\Event;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateEventRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title') && $this->input('title')) {
            $this->merge(['slug' => Str::slug((string) $this->input('title'))]);
        } elseif ($this->has('slug') && $this->input('slug')) {
            $this->merge(['slug' => Str::slug((string) $this->input('slug'))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $eventId = $this->route('event')?->id ?? null;

        return [
            'title' => ['sometimes', 'required', 'string', 'min:1', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', Rule::unique('events', 'slug')->ignore($eventId)],
            'category_id' => ['sometimes', 'nullable', 'uuid', 'exists:event_categories,id'],
            'image_id' => ['sometimes', 'nullable', 'uuid', 'exists:media_assets,id'],
            'description' => ['sometimes', 'nullable', 'string'],
            'location' => ['sometimes', 'nullable', 'string', 'max:255'],
            'start_at' => ['sometimes', 'nullable', 'date'],
            'end_at' => ['sometimes', 'nullable', 'date', 'after_or_equal:start_at'],
            'gdoc_url' => ['sometimes', 'nullable', 'url', 'max:2048'],
            'status' => ['sometimes', 'nullable', 'string', 'in:active,inactive,draft'],
            'price' => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'is_paid' => ['sometimes', 'nullable', 'boolean'],
            'is_featured' => ['sometimes', 'nullable', 'boolean'],
        ];
    }
}
