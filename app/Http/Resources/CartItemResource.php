<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'quantity' => (int) $this->quantity,
            'line_total_amount' => $this->line_total_amount,
            'product' => $this->whenLoaded('product', fn () => $this->product ? [
                'id' => $this->product->id,
                'title' => $this->product->title,
                'slug' => $this->product->slug,
                'type' => $this->product->type,
                'status' => $this->product->status,
                'price' => $this->product->price,
                'stock' => (int) $this->product->stock,
                'image' => $this->product->relationLoaded('image') && $this->product->image ? [
                    'id' => $this->product->image->id,
                    'url' => $this->product->image->url,
                ] : null,
            ] : null),
            'is_valid' => $this->is_valid,
            'invalid_reason' => $this->invalid_reason,
        ];
    }
}
