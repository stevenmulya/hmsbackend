<?php
// --- Controller for managing Partners (Final, Professional Version) ---
namespace App\Http\Controllers\Api\Admin;
use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource;
use App\Models\Partner;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class PartnerController extends Controller
{
    /**
     * Display a list of all partners, with search functionality.
     */
    public function index(Request $request) {
        $query = Partner::query();

        // --- SEARCH LOGIC ---
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('name', 'like', "%{$searchTerm}%")
                  ->orWhere('type', 'like', "%{$searchTerm}%");
            });
        }

        // We use get() here because the user might want drag-and-drop reordering later.
        // If the list becomes very long, change ->get() to ->paginate(20)
        return PartnerResource::collection($query->orderBy('order')->get());
    }

    /**
     * Store a newly created partner in storage.
     */
    public function store(Request $request) {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'link' => 'nullable|url',
            'type' => ['required', Rule::in(['vendor', 'client'])],
            'is_visible' => 'required|boolean',
            'logo' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);
        $validated['logo_path'] = $request->file('logo')->store('partners', 'public');
        $validated['order'] = Partner::max('order') + 1;
        $partner = Partner::create($validated);
        return new PartnerResource($partner);
    }

    /**
     * Display the specified partner.
     */
    public function show(Partner $partner) {
        return new PartnerResource($partner);
    }

    /**
     * Update the specified partner in storage.
     */
    public function update(Request $request, Partner $partner) {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'link' => 'nullable|url',
            'type' => ['sometimes', 'required', Rule::in(['vendor', 'client'])],
            'is_visible' => 'sometimes|required|boolean',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            if ($partner->logo_path) Storage::disk('public')->delete($partner->logo_path);
            $validated['logo_path'] = $request->file('logo')->store('partners', 'public');
        }
        
        $partner->update($validated);
        return new PartnerResource($partner);
    }

    /**
     * Remove the specified partner from storage.
     */
    public function destroy(Partner $partner) {
        if ($partner->logo_path) Storage::disk('public')->delete($partner->logo_path);
        $partner->delete();
        return response()->noContent();
    }

    /**
     * Update the order of all partners from a drag-and-drop interface.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer|exists:partners,id'
        ]);
        foreach ($validated['ordered_ids'] as $index => $id) {
            Partner::where('id', $id)->update(['order' => $index]);
        }
        return response()->json(['message' => 'Urutan partner berhasil diperbarui.']);
    }
}