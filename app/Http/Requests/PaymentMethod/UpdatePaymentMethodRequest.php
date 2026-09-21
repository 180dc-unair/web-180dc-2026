<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePaymentMethodRequest extends FormRequest
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
            'name' => ['sometimes', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('payment_methods', 'code')->ignore($this->route('paymentMethod')),
            ],
            'gateway' => ['sometimes', 'string', 'in:manual,midtrans'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.string' => 'Payment method name must be a valid text.',
            'code.string' => 'Payment method code must be a valid text.',
            'code.unique' => 'Payment method code already exists.',
            'gateway.in' => 'Gateway must be one of: manual, midtrans.',
            'is_active.boolean' => 'Active status must be true or false.',
        ];
    }
}
