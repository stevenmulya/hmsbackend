<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProductResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_category_id' => $this->sub_category_id,
            'product_name' => $this->product_name,
            'slug' => $this->slug,
            'product_code' => $this->product_code,
            'product_description' => $this->product_description,
            'product_size' => $this->product_size,
            'product_weight' => $this->product_weight,
            'product_price' => $this->product_price,
            'product_visibility' => (bool) ($this->product_visibility ?? true),
            'show_price' => (bool) ($this->show_price ?? true),
            'product_mainimage_url' => $this->product_mainimage ? 
                url('storage/' . $this->product_mainimage) : null,
            'tags' => $this->tags,
            'subCategory' => $this->whenLoaded('subCategory'),
            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(fn($image) => [
                    'id' => $image->id,
                    'path' => $image->path,
                    'url' => url('storage/' . $image->path)
                ]);
            }),
            'created_at' => $this->created_at ? $this->created_at->format('d-m-Y H:i') : null,
        ];
    }
}