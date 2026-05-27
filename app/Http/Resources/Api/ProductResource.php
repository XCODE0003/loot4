<?php

namespace App\Http\Resources\Api;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin Product */
class ProductResource extends JsonResource
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'slug' => $this->slug,
            'type' => $this->type->value,
            'price' => (float) $this->price,
            'compare_price' => $this->compare_price !== null ? (float) $this->compare_price : null,
            'currency' => $this->currency?->code,
            'status' => $this->status->value,
            'game' => $this->game?->name,
            'featured' => $this->featured,
            'auto_delivery' => $this->auto_delivery,
            'created_at' => $this->created_at?->toIso8601String(),
        ];
    }
}
