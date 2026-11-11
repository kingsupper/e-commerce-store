<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Filament\Forms\Components\Select;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'category_id'       => $this->category_id,
            'name'              => $this->name,
            'slug'              => $this->slug,
            'sku'               => $this->sku,
            'short_description' => $this->short_description,
            'description'       => $this->description,
            'price'             => $this->price,
            'sale_price'        => $this->sale_price,
            'stock_quantity'    => $this->stock_quantity,
            'is_active'         => $this->is_active,
            'is_featured'       => $this->is_featured,
            'category'          => new CategoryResource($this->whenLoaded('category')),
            'images' => ProductImageResource::collection($this->whenLoaded('images')),
            'created_at'        => $this->created_at,
            'updated_at'        => $this->updated_at,
            
        ];
    }



}
