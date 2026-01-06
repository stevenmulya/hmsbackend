<?php
// --- Controller for managing Product Sub-Categories ---
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\SubCategory;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubCategoryController extends Controller
{
    /**
     * Store a newly created sub-category in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:sub_categories,name',
            'category_id' => 'required|exists:categories,id',
        ]);
        $subCategory = SubCategory::create([
            'name' => $validatedData['name'],
            'category_id' => $validatedData['category_id'],
            'slug' => Str::slug($validatedData['name']),
        ]);
        return response()->json($subCategory, 201);
    }

    /**
     * Update the specified sub-category in storage.
     */
    public function update(Request $request, SubCategory $subCategory)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('sub_categories')->ignore($subCategory->id)],
            'category_id' => 'required|exists:categories,id',
        ]);
        $subCategory->update([
            'name' => $validatedData['name'],
            'category_id' => $validatedData['category_id'],
            'slug' => Str::slug($validatedData['name']),
        ]);
        return response()->json($subCategory);
    }

    /**
     * Remove the specified sub-category from storage.
     */
    public function destroy(SubCategory $subCategory)
    {
        // Professional check: Prevent deletion if sub-category has products.
        if ($subCategory->products()->exists()) {
            return response()->json(['message' => 'Subkategori tidak dapat dihapus karena masih memiliki produk terkait.'], 409);
        }
        $subCategory->delete();
        return response()->json(null, 204);
    }
}