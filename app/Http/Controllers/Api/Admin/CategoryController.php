<?php
// --- Controller for managing Product Categories (Final Version) ---
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    /**
     * Display a paginated list of product categories, with search functionality.
     */
    public function index(Request $request)
    {
        try {
            $query = Category::query();

            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where('name', 'like', "%{$searchTerm}%");
            }
            
            $categories = $query->with('subCategories')->latest()->paginate(20);
            
            return $categories;

        } catch (\Exception $e) {
            Log::error('Failed to fetch categories: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memuat kategori.'], 500);
        }
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);
        try {
            $category = Category::create([
                'name' => $validatedData['name'],
                'slug' => Str::slug($validatedData['name']),
            ]);
            return response()->json($category, 201);
        } catch (\Exception $e) {
            Log::error('Failed to create category: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan kategori.'], 500);
        }
    }

    /**
     * Display the specified category.
     */
    public function show(Category $category)
    {
        return $category->load('subCategories');
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, Category $category)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('categories')->ignore($category->id)],
        ]);
        try {
            $category->update([
                'name' => $validatedData['name'],
                'slug' => Str::slug($validatedData['name']),
            ]);
            return response()->json($category);
        } catch (\Exception $e) {
            Log::error("Failed to update category #{$category->id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui kategori.'], 500);
        }
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy(Category $category)
    {
        // Professional check: Prevent deletion if category is still in use.
        if ($category->subCategories()->withCount('products')->get()->sum('products_count') > 0) {
            return response()->json(['message' => 'Kategori tidak dapat dihapus karena masih memiliki produk terkait.'], 409);
        }
        $category->delete();
        return response()->json(null, 204);
    }
}