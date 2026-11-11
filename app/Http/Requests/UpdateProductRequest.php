<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $product = $this->route('product');
        $price = $this->input('price', $product->price);

        return [
            'category_id'       => ['nullable', 'integer', 'exists:categories,id'],
            'name'              => ['sometimes', 'required', 'string', 'max:255'],
            'slug'              => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('products', 'slug')->ignore($product->id),
            ],
            'sku'               => [
                'sometimes', 'required', 'string', 'max:255',
                Rule::unique('products', 'sku')->ignore($product->id),
            ],
            'short_description' => ['nullable', 'string'],
            'description'       => ['nullable', 'string'],
            'price'             => ['sometimes', 'required', 'numeric', 'min:0'],
            'sale_price'        => ['nullable', 'numeric', 'min:0', 'lt:' . $price],
            'stock_quantity'    => ['sometimes', 'integer', 'min:0'],
            'is_active'         => ['sometimes', 'boolean'],
            'is_featured'       => ['sometimes', 'boolean'],
        ];
    }
}
