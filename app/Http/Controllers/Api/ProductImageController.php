<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductImageRequest;
use App\Http\Resources\ProductImageResource;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class ProductImageController extends Controller
{
    // عرض صور منتج
    public function index(Product $product): AnonymousResourceCollection
    {
        return ProductImageResource::collection($product->images);
    }

    // رفع صورة جديدة للمنتج
    public function store(StoreProductImageRequest $request, Product $product): JsonResponse
    {
        $path = $request->file('image')->store('products', 'public');

        // لو دي أول صورة للمنتج، تبقى هي الرئيسية تلقائياً
        $isPrimary = $request->boolean('is_primary') || !$product->images()->exists();

        // لو الصورة الجديدة رئيسية، نشيل العلامة من الباقي
        if ($isPrimary) {
            $product->images()->update(['is_primary' => false]);
        }

        $image = $product->images()->create([
            'path' => $path,
            'alt_text' => $request->input('alt_text'),
            'is_primary' => $isPrimary,
            'sort_order' => $request->input('sort_order', 0),
        ]);

        return (new ProductImageResource($image))
            ->response()
            ->setStatusCode(201);
    }

    // حذف صورة
    public function destroy(Product $product, ProductImage $image): JsonResponse
    {
        // نتأكد إن الصورة تبع المنتج ده فعلاً
        abort_if($image->product_id !== $product->id, 404);

        Storage::disk('public')->delete($image->path);
        $image->delete();

        return response()->json(null, 204);
    }
}
