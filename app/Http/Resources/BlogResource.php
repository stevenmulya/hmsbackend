<?php
namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use App\Http\Resources\BlogCategoryResource; // 1. Pastikan Anda mengimpor BlogCategoryResource

class BlogResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'content' => $this->content,
            'is_visible' => $this->is_visible,
            'main_image_url' => $this->main_image ? Storage::url($this->main_image) : null,
            'blog_category_id' => $this->blog_category_id,
            'created_date' => $this->created_at->format('d F Y'),
            
            'author' => $this->whenLoaded('author', $this->author->name),
            
            // --- 2. PERBAIKAN DI SINI ---
            // Kembalikan seluruh resource kategori (yang berisi id, name, dan slug)
            'category' => new BlogCategoryResource($this->whenLoaded('category')),
            // --- AKHIR PERBAIKAN ---
            
            'images' => $this->whenLoaded('images', 
                $this->images->map(fn($image) => [
                    'id' => $image->id,
                    'path' => $image->path,
                    'url' => Storage::url($image->path)
                ])
            ),
        ];
    }
}