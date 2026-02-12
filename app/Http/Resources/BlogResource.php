<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BlogResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'content' => $this->content,
            'is_visible' => (bool) $this->is_visible,
            'blog_category_id' => $this->blog_category_id,
            
            // --- BAGIAN IMAGE DIPERBAIKI (MENGIKUTI GAYA PRODUCT) ---
            'main_image_url' => $this->main_image ? 
                url('storage/' . $this->main_image) : null,
            // --------------------------------------------------------

            'created_date' => $this->created_at ? $this->created_at->format('d F Y') : null,
            
            'author' => $this->whenLoaded('author', fn() => $this->author->name),
            
            // Pastikan Anda sudah punya BlogCategoryResource, jika belum, bisa dihapus/diganti
            'category' => new BlogCategoryResource($this->whenLoaded('category')),
            
            // --- BAGIAN GALLERY DIPERBAIKI (MENGIKUTI GAYA PRODUCT) ---
            'images' => $this->whenLoaded('images', function() {
                return $this->images->map(fn($image) => [
                    'id' => $image->id,
                    'path' => $image->path,
                    'url' => url('storage/' . $image->path)
                ]);
            }),
            // ----------------------------------------------------------
        ];
    }
}