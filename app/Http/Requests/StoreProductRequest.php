<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id'       => ['nullable', 'integer', 'exists:categories,id'],
            'name'              => ['required', 'string', 'max:255'],
            'slug'              => ['required', 'string', 'max:255', 'unique:products,slug'],
            'sku'               => ['required', 'string', 'max:255', 'unique:products,sku'],
            'short_description' => ['nullable', 'string'],
            'description'       => ['nullable', 'string'],
            'price'             => ['required', 'numeric', 'min:0'],
            'sale_price'        => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock_quantity'    => ['integer', 'min:0'],
            'is_active'         => ['boolean'],
            'is_featured'       => ['boolean'],
        ];
    }
}
