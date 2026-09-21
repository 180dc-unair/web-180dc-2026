<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PaymentResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $creation = $this->raw_response['creation'] ?? [];
        $instructions = match ($this->gateway) {
            'manual' => array_intersect_key($creation, array_flip(['bank', 'account_number', 'account_name', 'note'])),
            'midtrans' => [
                'bank' => strtoupper((string) data_get($creation, 'va_numbers.0.bank')),
                'va_number' => data_get($creation, 'va_numbers.0.va_number'),
            ],
            default => null,
        };

        return [
            'id' => $this->id,
            'order_id' => $this->order_id,
            'status' => $this->status,
            'amount' => $this->amount,
            'currency' => 'IDR',
            'gateway' => $this->gateway,
            'payment_url' => $this->payment_url,
            'gateway_reference' => $this->gateway_reference,
            'instructions' => $instructions,
            'proof' => new MediaAssetResource($this->whenLoaded('proof')),
            'expired_at' => $this->expired_at?->toISOString(),
            'paid_at' => $this->paid_at?->toISOString(),
            'payment_method' => new PaymentMethodResource($this->whenLoaded('method')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
