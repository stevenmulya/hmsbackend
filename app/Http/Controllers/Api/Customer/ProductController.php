<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * Display a paginated list of all VISIBLE products,
     * supporting filtering, searching, and sorting.
     */
    public function index(Request $request)
    {
        $query = Product::with(['subCategory.category', 'images'])
                       ->where('product_visibility', true);

        // --- FILTER KATEGORI/SUBKATEGORI ---
        if ($request->filled('subcategory_slug')) {
            $subCategorySlug = $request->input('subcategory_slug');
            $query->whereHas('subCategory', function ($q) use ($subCategorySlug) {
                $q->where('slug', $subCategorySlug);
            });
        }
        elseif ($request->filled('category_slug')) {
            $categorySlug = $request->input('category_slug');
            $query->whereHas('subCategory.category', function ($q) use ($categorySlug) {
                $q->where('slug', $categorySlug);
            });
        }

        // --- FILTER PENCARIAN (DIPERBAIKI) ---
        if ($request->filled('search')) {
            $searchTerm = strtolower($request->input('search'));
            $query->where(function ($q) use ($searchTerm) {
                $q->whereRaw('LOWER(product_name) LIKE ?', ["%{$searchTerm}%"])
                  ->orWhereRaw('LOWER(product_code) LIKE ?', ["%{$searchTerm}%"]);
                
                // Pastikan nama kolom 'tags' dan 'size' benar-benar ada di tabel 'products' Anda
                // Jika tidak, hapus juga baris di bawah ini
                
                // ->orWhereRaw('LOWER(tags) LIKE ?', ["%{$searchTerm}%"])
                // ->orWhereRaw('LOWER(size) LIKE ?', ["%{$searchTerm}%"]);
                
                // Kolom 'short_description' sudah dihapus karena menyebabkan error
            });
        }

        // --- PENGURUTAN ---
        $sort = $request->input('sort', 'latest');
        switch ($sort) {
            case 'price_asc':
                $query->orderBy('product_price', 'asc');
                break;
            case 'price_desc':
                $query->orderBy('product_price', 'desc');
                break;
            case 'name_asc':
                $query->orderBy('product_name', 'asc');
                break;
            case 'name_desc':
                $query->orderBy('product_name', 'desc');
                break;
            case 'latest':
            default:
                $query->latest();
                break;
        }

        // --- PAGINASI ---
        $limit = $request->input('limit', 12);
        $products = $query->paginate($limit);

        // --- RETURN RESPONSE ---
        return ProductResource::collection($products);
    }

    public function show(Product $product) 
    {
        if (!$product->product_visibility) {
            return response()->json(['message' => 'Produk tidak ditemukan.'], 404);
        }
        return new ProductResource($product->load(['subCategory.category', 'images']));
    }
}