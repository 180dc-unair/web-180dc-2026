<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class UpdateServiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [   
            'title' => ['sometimes', 'string', 'min:1', 'max:255'],
            'category_id' => ['sometimes', 'nullable', 'uuid', 'exists:service_categories,id'],
            'icon_id' => ['sometimes', 'nullable', 'uuid', 'exists:media_assets,id'],
            'short_description' => ['sometimes', 'nullable', 'string'],
            'description' => ['sometimes', 'nullable', 'string'],
            'is_featured' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
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
            'category_id.uuid' => 'The category ID must be a valid UUID.',
            'category_id.exists' => 'The selected category does not exist.',
            'icon_id.uuid' => 'The icon ID must be a valid UUID.',
            'icon_id.exists' => 'The selected icon does not exist.',
            'sort_order.integer' => 'The sort order must be a number.',
            'sort_order.min' => 'The sort order must be at least 0.',
        ];
    }
}
