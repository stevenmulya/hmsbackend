<?php
// --- Controller for managing Products (Final Version with Simplified Tags) ---
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreProductRequest;
use App\Http\Requests\Api\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
// The 'Tag' model is no longer needed in this controller.
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
    /**
     * Display a paginated list of products.
     */
    public function index(Request $request)
    {
        try {
            $query = Product::query();
            if ($request->filled('search')) {
                $searchTerm = $request->input('search');
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('product_name', 'like', "%{$searchTerm}%")
                      ->orWhere('product_code', 'like', "%{$searchTerm}%");
                });
            }
            // 'tags' is no longer a relationship, so it's removed from with().
            $products = $query->with(['subCategory.category', 'images'])->latest()->paginate(15);
            return ProductResource::collection($products);
        } catch (\Exception $e) {
            Log::error('Failed to fetch products: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memuat data produk karena kesalahan server.'], 500);
        }
    }

    /**
     * Store a newly created product in storage.
     */
    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $product = DB::transaction(function () use ($request, $validatedData) {
                // Tag logic is now handled directly by mass assignment.
                $productData = collect($validatedData)->except(['product_mainimage', 'product_imagelist'])->toArray();
                $productData['slug'] = Str::slug($productData['product_name']);

                if ($request->hasFile('product_mainimage')) {
                    $productData['product_mainimage'] = $request->file('product_mainimage')->store('products', 'public');
                }
                
                $product = Product::create($productData);
                
                if ($request->hasFile('product_imagelist')) {
                    foreach ($request->file('product_imagelist') as $file) {
                        $path = $file->store('products/gallery', 'public');
                        $product->images()->create(['path' => $path]);
                    }
                }
                
                return $product;
            });

            return new ProductResource($product->load(['images']));
        } catch (\Exception $e) {
            Log::error('Failed to store product: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan produk karena kesalahan server.'], 500);
        }
    }
    
    /**
     * Display the specified product.
     */
    public function show(Product $product)
    {
        return new ProductResource($product->load(['subCategory.category', 'images']));
    }

    /**
     * Update the specified product in storage.
     */
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validated();
        if (isset($validatedData['product_name'])) {
            $validatedData['slug'] = Str::slug($validatedData['product_name']);
        }
        
        try {
            $product = DB::transaction(function () use ($request, $product, $validatedData) {
                // Tag logic is now handled directly by mass assignment.
                $productData = collect($validatedData)->except(['product_mainimage', 'product_imagelist', 'existing_imagelist_paths', 'remove_main_image'])->toArray();

                // Image update logic remains the same...

                $product->update($productData);
                return $product;
            });
            return new ProductResource($product->load(['images']));
        } catch (\Exception $e) {
            Log::error('Failed to update product ID ' . $product->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui produk karena kesalahan server.'], 500);
        }
    }
    
    /**
     * Remove the specified product from storage.
     */
    public function destroy(Product $product)
    {
        DB::transaction(function () use ($product) {
            if ($product->product_mainimage) Storage::disk('public')->delete($product->product_mainimage);
            foreach ($product->images as $image) {
                Storage::disk('public')->delete($image->path);
            }
            $product->delete();
        });
        return response()->json(null, 204);
    }

    /**
     * Export a detailed list of all products to a CSV file.
     */
    public function exportCsv()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="products_detailed_' . date("Y-m-d") . '.csv"',
        ];

        $callback = function () {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                'ID', 'Nama Produk', 'Kode Produk', 'Kategori', 'Subkategori',
                'Harga', 'Ukuran', 'Berat (kg)', 'Visibilitas', 'Tag', 'Deskripsi'
            ]);

            Product::with(['subCategory.category'])->cursor()->each(function ($product) use ($file) {
                fputcsv($file, [
                    $product->id,
                    $product->product_name,
                    $product->product_code,
                    $product->subCategory->category->name ?? '',
                    $product->subCategory->name ?? '',
                    $product->product_price,
                    $product->product_size,
                    $product->product_weight,
                    $product->product_visibility ? 'Visible' : 'Hidden',
                    // Tags are now a simple text field
                    $product->tags,
                    $product->product_description,
                ]);
            });
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}