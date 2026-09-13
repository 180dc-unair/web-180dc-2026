<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderItemResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'item_type' => $this->item_type,
            'name' => $this->name,
            'quantity' => (int) $this->quantity,
            'unit_price' => $this->unit_price,
            'total_price' => $this->total_price,
            'product' => $this->whenLoaded('product', fn () => $this->product ? [
                'id' => $this->product->id,
                'slug' => $this->product->slug,
                'image' => $this->product->relationLoaded('image') && $this->product->image ? [
                    'id' => $this->product->image->id,
                    'url' => $this->product->image->url,
                ] : null,
            ] : null),
            'event' => $this->whenLoaded('event', fn () => $this->event ? [
                'id' => $this->event->id,
                'slug' => $this->event->slug,
                'image' => $this->event->relationLoaded('image') && $this->event->image ? [
                    'id' => $this->event->image->id,
                    'url' => $this->event->image->url,
                ] : null,
            ] : null),
        ];
    }
}
