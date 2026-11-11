<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartItemResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $unitPrice = $this->product->sale_price ?? $this->product->price;

        return [
            'id' => $this->id,
            'product_id' => $this->product_id,
            'quantity' => $this->quantity,
            'unit_price' => $unitPrice,
            'subtotal' => number_format($unitPrice * $this->quantity, 2, '.', ''),
            'product' => new ProductResource($this->whenLoaded('product')),
        ];
    }

}
