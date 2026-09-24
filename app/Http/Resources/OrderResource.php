<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'order_number' => $this->order_number,
            'status' => $this->status,
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'notes' => $this->notes,
            'currency' => $this->currency,
            'subtotal_amount' => $this->subtotal_amount,
            'total_amount' => $this->total_amount,
            'item_count' => $this->items_count ?? ($this->relationLoaded('items') ? $this->items->count() : null),
            'can_cancel' => $this->can_cancel,
            'can_pay' => $this->can_pay,
            'is_expired' => $this->is_expired,
            'paid_at' => $this->paid_at?->toISOString(),
            'expired_at' => $this->expired_at?->toISOString(),
            'items' => OrderItemResource::collection($this->whenLoaded('items')),
            'payments' => PaymentResource::collection($this->whenLoaded('payments')),
            'created_at' => $this->created_at?->toISOString(),
            'updated_at' => $this->updated_at?->toISOString(),
        ];
    }
}
