<?php
// --- Controller for Admin Authentication (Final Version) ---
// This file handles admin login, logout, and session verification.
// It includes robust error handling and professional practices.

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle an authentication attempt for an admin.
     */
    public function login(Request $request)
    {
        $request->validate([
            'admin_username' => 'required|string',
            'admin_password' => 'required|string',
        ]);

        $admin = Admin::where('admin_username', $request->admin_username)->first();

        if (!$admin || !Hash::check($request->admin_password, $admin->admin_password)) {
            throw ValidationException::withMessages([
                'admin_username' => ['Kredensial yang diberikan tidak cocok dengan catatan kami.'],
            ]);
        }
        
        $token = $admin->createToken('admin-auth-token', ['admin'])->plainTextToken;

        return response()->json([
            'message' => 'Admin login berhasil!',
            'token' => $token,
            'admin' => $admin
        ]);
    }

    /**
     * Get the authenticated admin user.
     * This method is now wrapped in a try-catch block for robust error handling.
     */
    public function me(Request $request)
    {
        try {
            // The middleware already ensures the user is authenticated.
            // We just need to return the user data.
            $admin = $request->user();
            
            if (!$admin) {
                 return response()->json(['message' => 'Admin tidak ditemukan.'], 404);
            }
            
            return response()->json($admin);

        } catch (\Exception $e) {
            // If any unexpected error occurs, log it and return a 500 error.
            Log::error('Failed to fetch authenticated admin: ' . $e->getMessage());
            return response()->json(['message' => 'Terjadi kesalahan saat mengambil data admin.'], 500);
        }
    }

    /**
     * Log the admin out (Invalidate the token).
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Admin logout berhasil.']);
    }
}