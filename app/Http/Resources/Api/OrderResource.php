<?php

namespace App\Http\Resources\Api;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Order */
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
            'email' => $this->email,
            'status' => $this->status->value,
            'payment_status' => $this->payment_status->value,
            'delivery_status' => $this->delivery_status->value,
            'currency' => $this->currency,
            'subtotal' => (float) $this->subtotal,
            'discount' => (float) $this->discount,
            'total' => (float) $this->total,
            'source' => $this->source,
            'items' => $this->whenLoaded('items', fn () => $this->items->map(fn ($item): array => [
                'product' => $item->product_name,
                'quantity' => $item->quantity,
                'price' => (float) $item->price,
            ])),
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
