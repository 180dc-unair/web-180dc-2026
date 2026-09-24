<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentMethodRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:255'],
            'code' => ['required', 'string', 'max:100', 'unique:payment_methods,code'],
            'gateway' => ['required', 'string', 'in:manual,midtrans'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Payment method name is required.',
            'code.required' => 'Payment method code is required.',
            'code.unique' => 'Payment method code already exists.',
            'gateway.required' => 'Gateway is required.',
            'gateway.in' => 'Gateway must be one of: manual, midtrans.',
        ];
    }
}
