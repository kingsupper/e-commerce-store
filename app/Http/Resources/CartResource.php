<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CartResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $items = $this->items;

        $total = $items->sum(function ($item) {
            $price = $item->product->sale_price ?? $item->product->price;
            return $price * $item->quantity;
        });

        return [
            'id' => $this->id,
            'items_count' => $items->sum('quantity'),
            'total' => number_format($total, 2, '.', ''),
            'items' => CartItemResource::collection($items),
        ];
    }
}
