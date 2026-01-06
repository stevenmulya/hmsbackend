<?php
// --- Controller for managing FAQs (Final Version with Search) ---
namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\FaqResource;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    /**
     * Display a list of FAQs, with search functionality.
     */
    public function index(Request $request)
    {
        $query = Faq::query();

        // --- SEARCH LOGIC ---
        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('question', 'like', "%{$searchTerm}%")
                  ->orWhere('answer', 'like', "%{$searchTerm}%");
            });
        }

        return FaqResource::collection($query->orderBy('order')->get());
    }

    /**
     * Store a newly created FAQ in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);
        $validated['order'] = Faq::max('order') + 1;
        $faq = Faq::create($validated);
        return new FaqResource($faq);
    }

    /**
     * Display the specified FAQ.
     */
    public function show(Faq $faq)
    {
        return new FaqResource($faq);
    }

    /**
     * Update the specified FAQ in storage.
     */
    public function update(Request $request, Faq $faq)
    {
        $validated = $request->validate([
            'question' => 'required|string',
            'answer' => 'required|string',
        ]);
        $faq->update($validated);
        return new FaqResource($faq);
    }

    /**
     * Remove the specified FAQ from storage.
     */
    public function destroy(Faq $faq)
    {
        $faq->delete();
        return response()->noContent();
    }

    /**
     * Update the order of all FAQs from a drag-and-drop interface.
     */
    public function updateOrder(Request $request)
    {
        $validated = $request->validate([
            'ordered_ids' => 'required|array',
            'ordered_ids.*' => 'integer|exists:faqs,id'
        ]);

        foreach ($validated['ordered_ids'] as $index => $id) {
            Faq::where('id', $id)->update(['order' => $index]);
        }
        
        return response()->json(['message' => 'Urutan FAQ berhasil diperbarui.']);
    }
}