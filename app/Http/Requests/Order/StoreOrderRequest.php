<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class StoreOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge([
            'idempotency_key' => $this->header('Idempotency-Key'),
        ]);
    }

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
            'customer_name' => ['sometimes', 'string', 'max:255'],
            'customer_email' => ['sometimes', 'email', 'max:255'],
            'notes' => ['sometimes', 'string', 'max:1000'],
            'idempotency_key' => ['nullable', 'uuid'],
            'items' => ['sometimes', 'array', 'min:1'],
            'items.*.item_type' => ['required_with:items', 'string', 'in:product,event'],
            'items.*.product_id' => ['required_if:items.*.item_type,product', 'uuid', 'exists:products,id'],
            'items.*.event_id' => ['required_if:items.*.item_type,event', 'uuid', 'exists:events,id'],
            'items.*.quantity' => ['required_with:items', 'integer', 'min:1', 'max:99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'customer_email.email' => 'The customer email must be a valid email address.',
            'idempotency_key.uuid' => 'The Idempotency-Key header must be a valid UUID.',
            'items.min' => 'At least one item is required when sending direct items.',
            'items.*.item_type.in' => 'Item type must be either product or event.',
            'items.*.product_id.exists' => 'The selected product does not exist.',
            'items.*.event_id.exists' => 'The selected event does not exist.',
            'items.*.quantity.min' => 'The quantity must be at least 1.',
            'items.*.quantity.max' => 'The quantity may not be greater than 99.',
        ];
    }
}
