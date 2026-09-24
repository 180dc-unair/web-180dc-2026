<?php

namespace App\Http\Requests\CartItem;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCartItemRequest extends FormRequest
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
            'quantity' => ['required', 'integer', 'min:1', 'max:99'],
        ];
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'The quantity is required.',
            'quantity.integer' => 'The quantity must be a number.',
            'quantity.min' => 'The quantity must be at least 1. Use DELETE to remove an item.',
            'quantity.max' => 'The quantity may not be greater than 99.',
        ];
    }
}
