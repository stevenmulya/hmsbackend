<?php

namespace App\Http\Controllers\Api\Customer; // Namespace Customer

use App\Http\Controllers\Controller;
use App\Http\Resources\PartnerResource; // Gunakan Resource yang sama
use App\Models\Partner;
use Illuminate\Http\Request;

class PartnerController extends Controller
{
    /**
     * Display a list of VISIBLE partners for the customer frontend.
     */
    public function index()
    {
        // Ambil hanya partner yang 'is_visible' = true
        $partners = Partner::where('is_visible', true) 
            ->orderBy('order') // Urutkan berdasarkan order
            ->get(); // Ambil semua yang visible

        // Kembalikan menggunakan Resource Collection
        return PartnerResource::collection($partners);
    }

    // Method lain (seperti show) bisa ditambahkan jika perlu
}