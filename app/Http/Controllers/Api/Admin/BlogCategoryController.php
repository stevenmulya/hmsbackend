<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogCategoryResource;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogCategoryController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = BlogCategory::query();
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where('name', 'like', "%{$searchTerm}%");
            }
            $categories = $query->latest()->paginate(20);
            return BlogCategoryResource::collection($categories);
        } catch (\Exception $e) {
            Log::error('Failed to fetch blog categories: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memuat kategori blog.'], 500);
        }
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:blog_categories,name',
        ]);

        try {
            $category = BlogCategory::create([
                'name' => $validatedData['name'],
                'slug' => Str::slug($validatedData['name']),
            ]);
            return new BlogCategoryResource($category);
        } catch (\Exception $e) {
            Log::error('Failed to create blog category: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan kategori blog.'], 500);
        }
    }

    public function show(BlogCategory $blogCategory)
    {
        return new BlogCategoryResource($blogCategory);
    }

    public function update(Request $request, BlogCategory $blogCategory)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('blog_categories')->ignore($blogCategory->id)],
        ]);

        try {
            $blogCategory->update([
                'name' => $validatedData['name'],
                'slug' => Str::slug($validatedData['name']),
            ]);
            return new BlogCategoryResource($blogCategory);
        } catch (\Exception $e) {
            Log::error("Failed to update blog category #{$blogCategory->id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui kategori blog.'], 500);
        }
    }

    public function destroy(BlogCategory $blogCategory)
    {
        try {
            if ($blogCategory->blogs()->exists()) {
                return response()->json([
                    'message' => 'Kategori tidak dapat dihapus karena masih digunakan oleh artikel.'
                ], 409);
            }
            $blogCategory->delete();
            return response()->json(['message' => 'Kategori berhasil dihapus.'], 200);
        } catch (\Exception $e) {
            Log::error("Failed to delete blog category #{$blogCategory->id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus kategori blog.'], 500);
        }
    }
}