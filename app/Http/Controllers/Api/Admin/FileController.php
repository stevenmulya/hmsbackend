<?php
// --- Controller for Secure File Downloads ---
// This controller ensures that only authenticated admins can download files
// from the protected storage directory, preventing unauthorized access.

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    /**
     * Handle a request to download a file from public storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|\Illuminate\Http\JsonResponse
     */
    public function download(Request $request)
    {
        // 1. Validate that the 'path' parameter exists.
        $request->validate(['path' => 'required|string']);
        $filePath = $request->input('path');

        // 2. CRITICAL SECURITY CHECK: Ensure the requested path is not malicious.
        if (Str::contains($filePath, '..') || !Storage::disk('public')->exists($filePath)) {
            return response()->json(['message' => 'File tidak ditemukan.'], 404);
        }

        // 3. If the file is valid, stream it as a download.
        return Storage::disk('public')->download($filePath);
    }
}