<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateClientRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'min:1', 'max:255'],
            'type' => ['sometimes', 'string', 'in:CASE_COLLABORATION,EVENT_COLLABORATION,MEDIA_PARTNER'],
            'logo_id' => ['sometimes', 'nullable', 'uuid', 'exists:media_assets,id'],
            'website_url' => ['sometimes', 'nullable', 'url'],
            'is_featured' => ['sometimes', 'boolean'],
            'sort_order' => ['sometimes', 'integer', 'min:0'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'The name must be a valid text.',
            'name.max' => 'The name may not be greater than 255 characters.',
            'type.in' => 'The type must be one of: CASE_COLLABORATION, EVENT_COLLABORATION, MEDIA_PARTNER.',
            'logo_id.uuid' => 'The logo ID must be a valid UUID.',
            'logo_id.exists' => 'The selected logo does not exist.',
            'website_url.url' => 'The website URL must be a valid URL.',
            'sort_order.integer' => 'The sort order must be a number.',
            'sort_order.min' => 'The sort order must be at least 0.',
        ];
    }
}
