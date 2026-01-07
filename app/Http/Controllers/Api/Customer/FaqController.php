<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Models\Faq;
use Illuminate\Http\Request;

class FaqController extends Controller
{
    public function index(Request $request)
    {
        $query = Faq::query();

        if ($request->filled('search')) {
            $searchTerm = $request->input('search');
            $query->where(function ($q) use ($searchTerm) {
                $q->where('question', 'like', "%{$searchTerm}%")
                  ->orWhere('answer', 'like', "%{$searchTerm}%");
            });
        }

        // Return data yang sudah diurutkan (order)
        $faqs = $query->orderBy('order')->get();

        return response()->json([
            'success' => true,
            'data' => $faqs
        ]);
    }
}