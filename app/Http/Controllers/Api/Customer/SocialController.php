<?php

namespace App\Http\Controllers\Api\Customer; // Pastikan namespace benar

use App\Http\Controllers\Controller;
use App\Http\Resources\SocialResource; // Asumsi Anda punya SocialResource
use App\Models\Social;                 // Import model Social
use Illuminate\Http\Request;

class SocialController extends Controller
{
    /**
     * Display a list of VISIBLE social links/contacts.
     *
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function index()
    {
        // Ambil hanya social links yang 'is_visible' = true
        $socials = Social::where('is_visible', true)
                         ->orderBy('platform') // Urutkan berdasarkan platform (opsional)
                         ->get(); // Ambil semua yang visible

        // Kembalikan menggunakan Resource Collection
        // Pastikan Anda sudah membuat SocialResource (php artisan make:resource SocialResource)
        // atau ganti dengan response()->json(['data' => $socials])
        return SocialResource::collection($socials);
    }

    // Tidak perlu method store, update, destroy, dll. untuk customer API
}