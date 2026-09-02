<?php

namespace App\Http\Requests\EventCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateEventCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name') && $this->input('name')) {
            $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
        }
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        $id = $this->route('eventCategory')?->id ?? $this->route('event_category')?->id ?? null;

        return [
            'name' => ['sometimes', 'required', 'string', 'min:1', 'max:255'],
            'slug' => ['sometimes', 'required', 'string', Rule::unique('event_categories', 'slug')->ignore($id)],
        ];
    }
}
