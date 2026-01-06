<?php
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\BlogResource;
use App\Models\Blog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class BlogController extends Controller
{
    public function index()
    {
        $blogs = Blog::with(['author', 'category'])->latest()->paginate(15);
        return BlogResource::collection($blogs);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:blogs,title',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'is_visible' => 'required|boolean',
            'main_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'imagelist' => 'nullable|array',
            'imagelist.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        try {
            $blog = DB::transaction(function () use ($request, $validated) {
                $blogData = $validated;
                $blogData['slug'] = Str::slug($validated['title']);
                $blogData['admin_id'] = auth()->id();
                if ($request->hasFile('main_image')) {
                    $blogData['main_image'] = $request->file('main_image')->store('blogs', 'public');
                }
                $blog = Blog::create($blogData);
                if ($request->hasFile('imagelist')) {
                    foreach ($request->file('imagelist') as $file) {
                        $path = $file->store('blogs/gallery', 'public');
                        $blog->images()->create(['path' => $path]);
                    }
                }
                return $blog;
            });
            return new BlogResource($blog->load(['author', 'category', 'images']));
        } catch (\Exception $e) {
            Log::error('Failed to store blog post: ' . $e->getMessage());
            return response()->json(['message' => 'Gagal menyimpan artikel.'], 500);
        }
    }

    public function show(Blog $blog)
    {
        return new BlogResource($blog->load(['author', 'category', 'images']));
    }

    public function update(Request $request, Blog $blog)
    {
        $validated = $request->validate([
            'title' => ['required','string','max:255', Rule::unique('blogs')->ignore($blog->id)],
            'blog_category_id' => 'required|exists:blog_categories,id',
            'description' => 'nullable|string|max:500',
            'content' => 'required|string',
            'is_visible' => 'required|boolean',
            'main_image' => 'nullable|sometimes|image|mimes:jpeg,png,jpg,webp|max:2048',
            'imagelist' => 'nullable|array',
            'imagelist.*' => 'image|mimes:jpeg,png,jpg,webp|max:2048',
            'remove_main_image' => 'nullable|boolean',
            'existing_imagelist_paths' => 'nullable|array',
            'existing_imagelist_paths.*' => 'string',
        ]);

        try {
            $blog = DB::transaction(function () use ($request, $blog, $validated) {
                $blogData = collect($validated)->except(['main_image', 'imagelist', 'remove_main_image', 'existing_imagelist_paths'])->toArray();
                $blogData['slug'] = Str::slug($validated['title']);

                if ($request->boolean('remove_main_image')) {
                    if ($blog->main_image) Storage::disk('public')->delete($blog->main_image);
                    $blogData['main_image'] = null;
                } elseif ($request->hasFile('main_image')) {
                    if ($blog->main_image) Storage::disk('public')->delete($blog->main_image);
                    $blogData['main_image'] = $request->file('main_image')->store('blogs', 'public');
                }

                if ($request->has('existing_imagelist_paths') || $request->hasFile('imagelist')) {
                    $existingPathsToKeep = $request->input('existing_imagelist_paths', []);
                    $blog->images()->whereNotIn('path', $existingPathsToKeep)->get()->each(function ($image) {
                        Storage::disk('public')->delete($image->path);
                        $image->delete();
                    });
                    if ($request->hasFile('imagelist')) {
                        foreach ($request->file('imagelist') as $file) {
                            $path = $file->store('blogs/gallery', 'public');
                            $blog->images()->create(['path' => $path]);
                        }
                    }
                }
                
                $blog->update($blogData);
                return $blog;
            });
            return new BlogResource($blog->load(['author', 'category', 'images']));
        } catch (\Exception $e) {
            Log::error("Failed to update blog #{$blog->id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal memperbarui artikel.'], 500);
        }
    }
    
    public function destroy(Blog $blog)
    {
        try {
            DB::transaction(function () use ($blog) {
                if ($blog->main_image) Storage::disk('public')->delete($blog->main_image);
                foreach ($blog->images as $image) {
                    Storage::disk('public')->delete($image->path);
                }
                $blog->delete();
            });
            return response()->noContent();
        } catch (\Exception $e) {
            Log::error("Failed to delete blog #{$blog->id}: " . $e->getMessage());
            return response()->json(['message' => 'Gagal menghapus artikel.'], 500);
        }
    }
}