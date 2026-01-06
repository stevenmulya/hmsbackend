<?php

namespace App\Http\Controllers\Api\Customer; // Pastikan namespace benar

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogCategoryResource; // Gunakan Resource yang Anda buat
use App\Models\BlogCategory;                 // Gunakan Model BlogCategory
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;      // Untuk logging error

class BlogCategoryController extends Controller
{
    /**
     * Display a list of blog categories.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        try {
            // Ambil semua kategori blog
            // Anda bisa tambahkan ->where('is_visible', true) jika ada
            $categories = BlogCategory::orderBy('name')->get();

            // Gunakan BlogCategoryResource yang sudah Anda buat
            return BlogCategoryResource::collection($categories);

        } catch (\Exception $e) {
            // --- PERBAIKAN DI SINI: Menghapus tanda kutip ekstra ---
            Log::error('Failed to fetch public blog categories: ' . $e->getMessage());
            // --- AKHIR PERBAIKAN ---
            return response()->json(['message' => 'Gagal memuat data kategori.'], 500);
        }
    }
}