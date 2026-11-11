<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCartItemRequest;
use App\Http\Requests\UpdateCartItemRequest;
use App\Http\Resources\CartResource;
use App\Models\CartItem;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CartController extends Controller
{
    // عرض السلة
    public function show(Request $request): JsonResponse
    {
        return (new CartResource($this->loadCart($request)))
            ->response()
            ->setStatusCode(200);
    }

    public function addItem(StoreCartItemRequest $request): JsonResponse
    {
        $cart = $request->user()->cart()->firstOrCreate([]);

        $item = $cart->items()->firstOrNew([
            'product_id' => $request->product_id,
        ]);

        $newQuantity = ($item->exists ? $item->quantity : 0) + $request->input('quantity', 1);

        $product = \App\Models\Product::findOrFail($request->product_id);

        if ($newQuantity > $product->stock_quantity) {
            return response()->json([
                'message' => "الكمية المطلوبة غير متوفرة. المتاح في المخزون هو {$product->stock_quantity} فقط."
            ], 400);
        }


        $item->quantity = min($newQuantity, 100);
        $item->save();

        return (new CartResource($this->loadCart($request)))
            ->response()
            ->setStatusCode(201);
    }

    public function updateItem(UpdateCartItemRequest $request, CartItem $item): CartResource
    {
        $this->ensureOwner($request, $item);

        $item->update(['quantity' => $request->quantity]);

        return new CartResource($this->loadCart($request));
    }


    public function removeItem(Request $request, CartItem $item): JsonResponse
    {
        $this->ensureOwner($request, $item);

        $item->delete();

        return response()->json(null, 204);
    }

    public function clear(Request $request): JsonResponse
    {
        $request->user()->cart?->items()->delete();

        return response()->json(null, 204);
    }

    private function loadCart(Request $request)
    {
        $cart = $request->user()->cart()->firstOrCreate([]);

        return $cart->load('items.product');
    }

    private function ensureOwner(Request $request, CartItem $item): void
    {
        abort_if($item->cart->user_id !== $request->user()->id, 404);
    }
}
