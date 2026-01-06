<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
// use App\Http\Resources\CategoryResource; // Hapus atau komentari use Resource
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Display a list of categories, optionally including subcategories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            $query = Category::query();

            // Eager Load Subcategories
            if ($request->filled('include') && in_array('subcategories', explode(',', $request->input('include')))) {
                 // Ganti 'subCategories' menjadi 'subcategories' sesuai nama method di model
                 $query->with(['subcategories' => function ($query) { 
                    $query->orderBy('name');
                }]);
            }

            // Opsional: Filter visibility jika ada kolomnya
            // $query->where('is_visible', true);

            $query->orderBy('name');
            $categories = $query->get();

            // --- PERUBAHAN DI SINI ---
            // Kembalikan data sebagai JSON biasa, bungkus dalam 'data' agar konsisten
            return response()->json(['data' => $categories]);
            // Bukan lagi: return CategoryResource::collection($categories);
            // --- AKHIR PERUBAHAN ---

        } catch (\Exception $e) {
            Log::error('Failed to fetch public categories: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memuat data kategori.'], 500);
        }
    }

    // Method 'show' bisa ditambahkan jika perlu
}