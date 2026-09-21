<?php

namespace App\Http\Requests\PaymentMethod;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListPaymentMethodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'is_active' => ['sometimes', 'boolean'],
            'gateway' => ['sometimes', 'string', Rule::in(['manual', 'midtrans'])],
        ];
    }
}
