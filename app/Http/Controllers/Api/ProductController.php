<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        $products = Product::with(['category', 'images'])
            ->latest()
            ->paginate(15);

        return ProductResource::collection($products);
    }

    public function store(StoreProductRequest $request): JsonResponse
    {
        $product = Product::create($request->validated());
        $product->refresh();

        return (new ProductResource($product->load(['category', 'images'])))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Product $product): ProductResource
    {
        return new ProductResource($product->load(['category', 'images']));
    }


    public function update(Request $request, Product $product)
    {
        // 1. التحقق من البيانات (Validation)
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',

            // تجاهل الـ ID الخاص بالمنتج الحالي أثناء فحص التكرار
            'slug' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('products', 'slug')->ignore($product->id),
            ],
            'sku' => [
                'sometimes',
                'required',
                'string',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'price' => 'sometimes|required|numeric',
            'stock_quantity' => 'sometimes|required|integer',
            // أضف باقي الحقول هنا...
        ], [
            // رسائل الخطأ باللغة العربية (اختياري)
            'slug.unique' => 'هذا الـ Slug مستخدم بالفعل في منتج آخر.',
            'sku.unique' => 'رمز المنتج (SKU) هذا مسجل بالفعل لمنتج آخر.',
        ]);

        // 2. تحديث بيانات المنتج
        $product->update($validatedData);

        // 3. إرجاع استجابة نجاح (Response)
        return response()->json([
            'message' => 'تم تحديث المنتج بنجاح',
            'product' => $product
        ], 200);
    }

    public function destroy(Product $product): JsonResponse
    {
        $product->delete();

        return response()->json(null, 204);
    }
}
