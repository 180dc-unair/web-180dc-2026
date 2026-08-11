<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('title')) {
            $this->merge(['slug' => Str::slug($this->title)]);
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:1', 'max:255'],
            'slug' => ['required', 'string', 'unique:products,slug'],
            'category_id' => ['nullable', 'uuid', 'exists:product_categories,id'],
            'image_id' => ['nullable', 'uuid', 'exists:media_assets,id'],
            'type' => ['required', 'string', 'in:digital,physical'],
            'status' => ['nullable', 'string', 'in:active,inactive'],
            'short_description' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'is_featured' => ['nullable', 'boolean'],
            'is_best_seller' => ['nullable', 'boolean'],
            'digital_file_url' => ['nullable', 'string', 'url'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.required' => 'The title field is required.',
            'title.string' => 'The title must be a valid text.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'slug.unique' => 'A product with this title already exists. Please use a different title.',
            'category_id.uuid' => 'The category ID must be a valid UUID.',
            'category_id.exists' => 'The selected category does not exist.',
            'image_id.uuid' => 'The image ID must be a valid UUID.',
            'image_id.exists' => 'The selected image does not exist.',
            'type.required' => 'The type field is required.',
            'type.in' => 'The type must be either digital or physical.',
            'status.in' => 'The status must be either active or inactive.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'stock.integer' => 'The stock must be a number.',
            'stock.min' => 'The stock must be at least 0.',
        ];
    }
}
