<?php

namespace App\Http\Requests\ServiceCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateServiceCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('name')) {
            $this->merge(['slug' => Str::slug($this->name)]);
        }
    }

    public function rules(): array
    {
        $category = $this->route('serviceCategory');

        return [
            'name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'slug' => ['sometimes', 'string', 'max:255', Rule::unique('service_categories', 'slug')->ignore($category)],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
