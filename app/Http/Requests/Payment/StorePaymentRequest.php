<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'payment_method_code' => ['required', 'string', 'exists:payment_methods,code'],
            'idempotency_key' => ['sometimes', 'uuid'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'payment_method_code.required' => 'Payment method code is required.',
            'payment_method_code.string' => 'Payment method code must be a valid text.',
            'payment_method_code.exists' => 'Selected payment method does not exist.',
            'idempotency_key.uuid' => 'Idempotency key must be a valid UUID.',
        ];
    }
}
