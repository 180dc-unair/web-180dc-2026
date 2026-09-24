<?php

namespace App\Http\Requests\Payment;

use Illuminate\Foundation\Http\FormRequest;

class UploadPaymentProofRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, mixed> */
    public function rules(): array
    {
        return [
            'proof' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'proof.required' => 'Payment proof is required.',
            'proof.image' => 'Payment proof must be an image.',
            'proof.mimes' => 'Payment proof must be a JPG, JPEG, PNG, or WEBP image.',
            'proof.max' => 'Payment proof may not be greater than 10 MB.',
        ];
    }
}
