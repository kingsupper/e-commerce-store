<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductImageRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image'      => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5400'],
            'alt_text'   => ['nullable', 'string', 'max:255'],
            'is_primary' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ];
    }
}
