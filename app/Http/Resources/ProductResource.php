<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
// Impor resource lain jika Anda menggunakannya
// use App\Http\Resources\SubCategoryResource; 
// use App\Http\Resources\ProductImageResource;

class ProductResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * @param  \Illuminate\Http\Request  $request
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sub_category_id' => $this->sub_category_id,
            'product_name' => $this->product_name,
            'slug' => $this->slug,
            'product_code' => $this->product_code,
            'product_description' => $this->product_description, // Ini untuk Section B
            'product_size' => $this->product_size,
            'product_weight' => $this->product_weight,
            'product_price' => $this->product_price,
            'product_visibility' => $this->product_visibility,
            'product_mainimage_url' => $this->product_mainimage ? Storage::url($this->product_mainimage) : null,
            'tags' => $this->tags,
            
            // --- PERBAIKAN DI SINI: Tambahkan data subCategory ---
            // Ini akan menyertakan seluruh objek SubCategory (termasuk .slug)
            // saat Anda me-load-nya di controller (misal: $product->load('subCategory.category'))
            'subCategory' => $this->whenLoaded('subCategory'),
            // Jika Anda punya SubCategoryResource, gunakan ini:
            // 'subCategory' => new SubCategoryResource($this->whenLoaded('subCategory')),
            // --- AKHIR PERBAIKAN ---

            'images' => $this->whenLoaded('images', 
                $this->images->map(fn($image) => [
                    'id' => $image->id,
                    'path' => $image->path,
                    'url' => Storage::url($image->path)
                ])
            ),
            'created_at' => $this->created_at->format('d-m-Y H:i'),
        ];
    }
}