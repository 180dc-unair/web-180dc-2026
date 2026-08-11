<?php

namespace App\Http\Requests\Product;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class UpdateProductRequest extends FormRequest
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
            'title' => ['sometimes', 'string', 'min:1', 'max:255'],
            'slug' => ['sometimes', 'string', \Illuminate\Validation\Rule::unique('products', 'slug')->ignore($this->route('product'))],
            'category_id' => ['sometimes', 'nullable', 'uuid', 'exists:product_categories,id'],
            'image_id' => ['sometimes', 'nullable', 'uuid', 'exists:media_assets,id'],
            'type' => ['sometimes', 'string', 'in:digital,physical'],
            'status' => ['sometimes', 'string', 'in:active,inactive'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'price' => ['sometimes', 'numeric', 'min:0'],
            'stock' => ['sometimes', 'integer', 'min:0'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_best_seller' => ['sometimes', 'boolean'],
            'digital_file_url' => ['sometimes', 'nullable', 'string', 'url'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'title.string' => 'The title must be a valid text.',
            'title.max' => 'The title may not be greater than 255 characters.',
            'slug.unique' => 'A product with this title already exists. Please use a different title.',
            'category_id.uuid' => 'The category ID must be a valid UUID.',
            'category_id.exists' => 'The selected category does not exist.',
            'image_id.uuid' => 'The image ID must be a valid UUID.',
            'image_id.exists' => 'The selected image does not exist.',
            'type.in' => 'The type must be either digital or physical.',
            'status.in' => 'The status must be either active or inactive.',
            'price.numeric' => 'The price must be a number.',
            'price.min' => 'The price must be at least 0.',
            'stock.integer' => 'The stock must be a number.',
            'stock.min' => 'The stock must be at least 0.',
        ];
    }
}
