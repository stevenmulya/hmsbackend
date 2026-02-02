<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Admin\StoreProductRequest;
use App\Http\Requests\Api\Admin\UpdateProductRequest;
use App\Http\Resources\ProductResource;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductController extends Controller
{
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

            if ($request->filled('category')) {
                $categoryId = $request->input('category');
                $query->whereHas('subCategory', function ($q) use ($categoryId) {
                    $q->where('category_id', $categoryId);
                });
            }

            $products = $query->with(['subCategory.category', 'images'])
                              ->latest()
                              ->paginate(15);

            return ProductResource::collection($products);
        } catch (\Exception $e) {
            Log::error('Failed to fetch products: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memuat data produk.'], 500);
        }
    }

    public function toggleSinglePrice(Request $request, $id)
    {
        try {
            $product = Product::findOrFail($id);
            $status = $request->boolean('show_price');
            
            $product->update(['show_price' => $status]);

            return response()->json([
                'message' => 'Status harga berhasil diperbarui.',
                'status' => $status,
                'data' => new ProductResource($product)
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui status.'], 500);
        }
    }

    public function toggleAllPrices(Request $request)
    {
        try {
            $status = $request->boolean('show_price');
            Product::query()->update(['show_price' => $status]);

            return response()->json([
                'message' => 'Status harga semua produk berhasil diperbarui.',
                'status' => $status
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => 'Gagal memperbarui status global.'], 500);
        }
    }

    public function store(StoreProductRequest $request)
    {
        $validatedData = $request->validated();
        
        try {
            $product = DB::transaction(function () use ($request, $validatedData) {
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
            return response()->json(['message' => 'Gagal menyimpan produk.'], 500);
        }
    }

    public function show(Product $product)
    {
        return new ProductResource($product->load(['subCategory.category', 'images']));
    }
    
    public function update(UpdateProductRequest $request, Product $product)
    {
        $validatedData = $request->validated();
        
        if (isset($validatedData['product_name'])) {
            $validatedData['slug'] = Str::slug($validatedData['product_name']);
        }
        
        try {
            $product = DB::transaction(function () use ($request, $product, $validatedData) {
                // Pastikan _method tidak ikut masuk ke database
                $productData = collect($validatedData)->except(['product_mainimage', 'product_imagelist', 'existing_imagelist_paths', 'remove_main_image', '_method'])->toArray();

                if ($request->has('show_price')) {
                    $productData['show_price'] = $request->boolean('show_price');
                }

                if ($request->boolean('remove_main_image') && $product->product_mainimage) {
                    Storage::disk('public')->delete($product->product_mainimage);
                    $productData['product_mainimage'] = null;
                }

                if ($request->hasFile('product_mainimage')) {
                    if ($product->product_mainimage) {
                        Storage::disk('public')->delete($product->product_mainimage);
                    }
                    $productData['product_mainimage'] = $request->file('product_mainimage')->store('products', 'public');
                }

                if ($request->has('existing_imagelist_paths')) {
                    $existingPaths = $request->input('existing_imagelist_paths', []);
                    $imagesToDelete = $product->images()->whereNotIn('path', $existingPaths)->get();
                    foreach ($imagesToDelete as $image) {
                        Storage::disk('public')->delete($image->path);
                        $image->delete();
                    }
                }

                if ($request->hasFile('product_imagelist')) {
                    foreach ($request->file('product_imagelist') as $file) {
                        $path = $file->store('products/gallery', 'public');
                        $product->images()->create(['path' => $path]);
                    }
                }

                $product->update($productData);
                return $product;
            });

            return new ProductResource($product->load(['images']));
        } catch (\Exception $e) {
            Log::error('Failed to update product: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui produk.'], 500);
        }
    }
    
    public function destroy(Product $product)
    {
        try {
            DB::transaction(function () use ($product) {
                if ($product->product_mainimage) {
                    Storage::disk('public')->delete($product->product_mainimage);
                }
                foreach ($product->images as $image) {
                    Storage::disk('public')->delete($image->path);
                }
                $product->delete();
            });
            return response()->json(null, 204);
        } catch (\Exception $e) {
            Log::error('Failed to delete product: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus produk.'], 500);
        }
    }

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
                'Harga', 'Ukuran', 'Berat (kg)', 'Visibilitas', 'Tampilkan Harga', 'Tag', 'Deskripsi'
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
                    $product->show_price ? 'Yes' : 'No',
                    $product->tags,
                    $product->product_description,
                ]);
            });
            fclose($file);
        };

        return new StreamedResponse($callback, 200, $headers);
    }
}