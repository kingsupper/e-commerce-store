<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrderResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'total' => (float) $this->total,
            'address_id' => $this->address_id,
            'items' => $this->items->map(fn($item) => [
                'id' => $item->id,
                'product_id' => $item->product_id,
                'name' => $item->product->name ?? null,
                'quantity' => $item->quantity,
                'price' => $item->price,
                'subtotal' => number_format($item->price * $item->quantity, 2, '.', ''),
                'payment' => $this->payment ? [
                    'method' => $this->payment->payment_method,
                    'status' => $this->payment->status,
                    'amount' => number_format($this->payment->amount, 2, '.', ''),
                ] : null,
            ]),
            'created_at' => $this->created_at,
        ];
    }
}
