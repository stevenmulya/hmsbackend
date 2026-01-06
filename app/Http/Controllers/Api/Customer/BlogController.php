<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
// Pastikan ini adalah model kategori blog Anda yang benar
use App\Models\BlogCategory; // Ganti jika nama modelnya Category
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Import Log

class BlogController extends Controller
{
    /**
     * Display a list of blogs, optionally filtered and sorted.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Query dasar untuk Blog
            $query = Blog::query();

            // Eager load relasi yang mungkin dibutuhkan (opsional untuk index)
            $query->with(['category']); // Muat kategori untuk ditampilkan di card (jika perlu)

            // Filter berdasarkan category_slug jika parameter ada
            if ($request->filled('category_slug')) {
                $categorySlug = $request->input('category_slug');
                // Asumsi relasi di Model Blog bernama 'category'
                $query->whereHas('category', function ($q) use ($categorySlug) {
                    $q->where('slug', $categorySlug);
                });
            }

            // LOGIKA PENCARIAN
            if ($request->filled('search')) {
                $searchTerm = strtolower($request->input('search'));
                $query->where(function ($q) use ($searchTerm) {
                    $q->whereRaw('LOWER(title) LIKE ?', ["%{$searchTerm}%"])
                      ->orWhereRaw('LOWER(description) LIKE ?', ["%{$searchTerm}%"])
                      ->orWhereRaw('LOWER(content) LIKE ?', ["%{$searchTerm}%"]);
                });
            }

            // PENGURUTAN (Sesuai Frontend)
            $sort = $request->input('sort', 'latest');
            switch ($sort) {
                case 'oldest':
                    $query->orderBy('created_at', 'asc');
                    break;
                case 'name_asc':
                    $query->orderBy('title', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('title', 'desc');
                    break;
                case 'latest':
                default:
                    $query->latest();
                    break;
            }

            // Pembatasan jumlah hasil (limit)
            $limit = $request->input('limit', 9);
            $blogs = $query->paginate($limit); 

            // Mengembalikan data menggunakan Resource
            return BlogResource::collection($blogs);
        
        } catch (\Exception $e) {
            Log::error('Failed to fetch public blogs: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memuat data blog.'], 500);
        }
    }

    /**
     * Display a single blog post by its slug.
     *
     * @param  \App\Models\Blog  $blog
     * @return \App\Http\Resources\BlogResource|\Illuminate\Http\JsonResponse
     */
    public function show(Blog $blog)
    {
        // (Opsional) Tambahkan pengecekan status publish jika ada
        // if (!$blog->is_visible) {
        //    return response()->json(['message' => 'Blog post not found.'], 404);
        // }

        // --- PERBAIKAN DI SINI ---
        // Muat relasi yang dibutuhkan oleh frontend (Section A & B)
        $blog->load(['category', 'images', 'author']); // Asumsi 'author' juga ada
        // --- AKHIR PERBAIKAN ---

        return new BlogResource($blog);
    }
}