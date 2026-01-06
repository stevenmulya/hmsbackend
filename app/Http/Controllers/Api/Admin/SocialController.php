<?php
// --- Controller for managing Social Links (Final Version with Search) ---
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialResource;
use App\Models\Social;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class SocialController extends Controller
{
    /**
     * Display a list of all social links, with search functionality.
     */
    public function index(Request $request)
    {
        $query = Social::query();

        // --- SEARCH LOGIC ---
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('platform', 'like', "%{$searchTerm}%");
            });
        }

        return SocialResource::collection($query->latest()->get());
    }

    /**
     * Store a newly created social link in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'platform' => 'required|string|max:100',
            'url' => 'required|url',
            'is_visible' => 'required|boolean',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:1024',
        ]);

        if ($request->hasFile('logo')) {
            $validated['logo_path'] = $request->file('logo')->store('socials', 'public');
        }

        $social = Social::create($validated);
        return new SocialResource($social);
    }

    /**
     * Display the specified social link.
     */
    public function show(Social $social)
    {
        return new SocialResource($social);
    }

    /**
     * Update the specified social link in storage.
     */
    public function update(Request $request, Social $social)
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'platform' => 'sometimes|required|string|max:100',
            'url' => 'sometimes|required|url',
            'is_visible' => 'sometimes|required|boolean',
            'logo' => 'nullable|image|mimes:png,jpg,jpeg,svg|max:1024',
        ]);

        if ($request->has('is_visible')) {
            $validated['is_visible'] = filter_var($request->is_visible, FILTER_VALIDATE_BOOLEAN);
        }

        if ($request->hasFile('logo')) {
            if ($social->logo_path) Storage::disk('public')->delete($social->logo_path);
            $validated['logo_path'] = $request->file('logo')->store('socials', 'public');
        }
        
        $social->update($validated);
        return new SocialResource($social);
    }

    /**
     * Remove the specified social link from storage.
     */
    public function destroy(Social $social)
    {
        if ($social->logo_path) Storage::disk('public')->delete($social->logo_path);
        $social->delete();
        return response()->noContent();
    }
}